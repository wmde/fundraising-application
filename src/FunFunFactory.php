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

	/**
	 * @var \Pimple
	 */
	private $pimple;

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
		$this->pimple = $this->newPimple();
	}

	private function newPimple(): \Pimple {
		$pimple = new \Pimple();

		$pimple['dbal_connection'] = $pimple->share( function() {
			return DriverManager::getConnection( $this->config['db'] );
		} );

		$pimple['request_repository'] = $pimple->share( function() {
			return new DoctrineRequestRepository( $this->getConnection() );
		} );

		$pimple['request_validator'] = $pimple->share( function() {
			return new RequestValidator( new MailValidator( MailValidator::TEST_WITH_MX ) );
		} );

		$pimple['mw_api'] = $pimple->share( function() {
			return new MediawikiApi(
				$this->config['cms-wiki-api-url'],
				$this->newGuzzleClient()
			);
		} );

		$pimple['twig'] = $pimple->share( function() {
			$options = [];

			if ( $this->config['enable-twig-cache'] ) {
				$options['cache'] = __DIR__ . '/../app/cache';
			}

			return new Twig_Environment(
				new Twig_Loader_Filesystem( __DIR__ . '/../app/templates' ),
				$options
			);
		} );

		return $pimple;
	}

	public function getConnection(): Connection {
		return $this->pimple['dbal_connection'];
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
		return $this->pimple['request_repository'];
	}

	public function setRequestRepository( RequestRepository $requestRepository ) {
		$this->pimple['request_repository'] = $requestRepository;
	}

	private function newRequestValidator(): RequestValidator {
		return $this->pimple['request_validator'];
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
			$this->getTwig(),
			'DisplayPageLayout.twig'
		) );
	}

	private function getTwig(): Twig_Environment {
		return $this->pimple['twig'];
	}

	private function newPageRetriever(): PageRetriever {
		return new ApiBasedPageRetriever(
			$this->getMediaWikiApi(),
			new ApiUser( $this->config['cms-wiki-user'], $this->config['cms-wiki-password'] ),
			$this->newLogger()
		);
	}

	private function getMediaWikiApi(): MediawikiApi {
		return $this->pimple['mw_api'];
	}

	public function setMediaWikiApi( MediawikiApi $api ) {
		$this->pimple['mw_api'] = $api;
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

	public function newBankDataConverter() {
		return new BankDataConverter( $this->config['bank-data-file'] );
	}

	public function setRequestValidator( RequestValidator $requestValidator ) {
		$this->pimple['request_validator'] = $requestValidator;
	}

	public function setPageTitlePrefix( string $prefix ) {
		$this->config['cms-wiki-title-prefix'] = $prefix;
	}
}
