<?php

namespace WMDE\Fundraising\Frontend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use FileFetcher\FileFetcher;
use FileFetcher\SimpleFileFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\Guzzle\MiddlewareFactory;
use Mediawiki\Api\MediawikiApi;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Twig_Environment;
use Twig_Loader_Filesystem;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\DoctrineRequestRepository;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;
use WMDE\Fundraising\Frontend\Domain\RequestRepository;
use WMDE\Fundraising\Frontend\Domain\RequestValidator;
use WMDE\Fundraising\Frontend\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\PageRetriever\ApiBasedPageRetriever;
use WMDE\Fundraising\Frontend\PageRetriever\PageRetriever;
use WMDE\Fundraising\Frontend\Presenters\DisplayPagePresenter;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageContentModifier;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\Frontend\UseCases\CheckIban\CheckIbanUseCase;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanUseCase;
use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;
use WMDE\Fundraising\Store\Factory as StoreFactory;
use WMDE\Fundraising\Store\Installer;

/**
 * @licence GNU GPL v2+
 */
class FunFunFactory {

	private $config;

	private $connection;
	private $fileFetcher;
	private $requestValidator;
	private $mwApi;

	private $requestRepository;

	/**
	 * @param array $config
	 * - db: DBAL connection parameters
	 * - cms-wiki-url
	 * - bank-data-file: path to file to be used by bank data validation library
	 * - cms-wiki-api-url
	 * - cms-wiki-user
	 * - cms-wiki-password
	 * - enable-twig-cache: boolean
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getConnection(): Connection {
		if ( $this->connection === null ) {
			$this->connection = $this->newConnection();
		}

		return $this->connection;
	}

	private function newConnection(): Connection {
		return DriverManager::getConnection( $this->config['db'] );
	}

	public function newInstaller(): Installer {
		return ( new StoreFactory( $this->getConnection() ) )->newInstaller();
	}

	public function newValidateEmailUseCase(): ValidateEmailUseCase {
		return new ValidateEmailUseCase();
	}

	public function newListCommentsUseCase(): ListCommentsUseCase {
		return new ListCommentsUseCase( $this->newCommentRepository() );
	}

	private function newCommentRepository(): CommentRepository {
		return new InMemoryCommentRepository( [] ); // TODO
	}

	private function newRequestRepository(): RequestRepository {
		if ( $this->requestRepository === null ) {
			$this->requestRepository = new DoctrineRequestRepository( $this->getConnection() );
		}

		return $this->requestRepository;
	}

	public function setRequestRepository( RequestRepository $requestRepository ) {
		$this->requestRepository = $requestRepository;
	}

	private function newRequestValidator(): RequestValidator {
		if ( $this->requestValidator === null ) {
			$this->requestValidator = new RequestValidator( new MailValidator( MailValidator::TEST_WITH_MX ) );
		}
		return $this->requestValidator;
	}

	public function newDisplayPageUseCase(): DisplayPageUseCase {
		return new DisplayPageUseCase(
			$this->newPageRetriever(),
			$this->newPageContentModifier(),
			$this->config['cms-wiki-title-prefix']
		);
	}

	public function newDisplayPagePresenter(): DisplayPagePresenter {
		return new DisplayPagePresenter( new TwigTemplate(
			$this->newTwig(),
			'DisplayPageLayout.twig'
		) );
	}

	private function newTwig() {
		$options = [];

		if ( $this->config['enable-twig-cache'] ) {
			$options['cache'] = __DIR__ . '/../app/cache';
		}

		return new Twig_Environment(
			new Twig_Loader_Filesystem( __DIR__ . '/../app/templates' ),
			$options
		);
	}

	private function newPageRetriever(): PageRetriever {
		return new ApiBasedPageRetriever(
			$this->getMediaWikiApi(),
			new ApiUser( $this->config['cms-wiki-user'], $this->config['cms-wiki-password'] ),
			$this->newLogger()
		);
	}

	private function getMediaWikiApi() {
		if ( $this->mwApi === null ) {
			$this->mwApi = new MediawikiApi(
				$this->config['cms-wiki-api-url'],
				$this->newGuzzleClient()
			);
		}

		return $this->mwApi;
	}

	public function setMediaWikiApi( MediawikiApi $api ) {
		$this->mwApi = $api;
	}

	private function newGuzzleClient(): ClientInterface {
		$middlewareFactory = new MiddlewareFactory();
		$middlewareFactory->setLogger( $this->newLogger() );

		$handlerStack = HandlerStack::create( new CurlHandler() );
		$handlerStack->push( $middlewareFactory->retry() );

		return new Client( [
			'cookies' => true,
			'handler' => $handlerStack,
			'headers' => [ 'User-Agent' => 'WMDE Fundraising Frontend' ],
		] );
	}

	private function newLogger(): LoggerInterface {
		return new NullLogger(); // TODO
	}

	private function newPageContentModifier(): PageContentModifier {
		return new PageContentModifier(
			$this->newLogger()
		);
	}

	public function newAddSubscriptionUseCase(): AddSubscriptionUseCase {
		return new AddSubscriptionUseCase( $this->newRequestRepository(), $this->newRequestValidator() );
	}

	public function newCheckIbanUseCase(): CheckIbanUseCase {
		return new CheckIbanUseCase( $this->newBankDataConverter() );
	}

	public function newGenerateIbanUseCase(): GenerateIbanUseCase {
		return new GenerateIbanUseCase( $this->newBankDataConverter() );
	}

	public function newIbanPresenter(): IbanPresenter {
		return new IbanPresenter();
	}

	public function newBankDataConverter() {
		return new BankDataConverter( $this->config['bank-data-file'] );
	}

	private function getFileFetcher(): FileFetcher {
		if ( $this->fileFetcher === null ) {
			$this->fileFetcher = new SimpleFileFetcher();
		}

		return $this->fileFetcher;
	}

	/**
	 * Should only be used by test setup code!
	 */
	public function setFileFetcher( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function setRequestValidator( RequestValidator $requestValidator ) {
		$this->requestValidator = $requestValidator;
	}

	public function setPageTitlePrefix( string $prefix ) {
		$this->config['cms-wiki-title-prefix'] = $prefix;
	}
}
