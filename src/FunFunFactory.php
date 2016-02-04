<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\Guzzle\MiddlewareFactory;
use Mediawiki\Api\MediawikiApi;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swift_MailTransport;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository;
use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineSubscriptionRepository;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListRssPresenter;
use WMDE\Fundraising\Frontend\Presentation\Content\WikiContentProvider;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHTMLPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJSONPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\GetInTouchHTMLPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHTMLPresenter;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\DataAccess\ApiBasedPageRetriever;
use WMDE\Fundraising\Frontend\Domain\PageRetriever;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DisplayPagePresenter;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier;
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
	 * - operator-email: used as sender when sending emails
	 * - operator-displayname: used as sender when sending emails
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

		$pimple['entity_manager'] = $pimple->share( function() {
			return ( new StoreFactory( $this->getConnection() ) )->getEntityManager();
		} );

		$pimple['request_repository'] = $pimple->share( function() {
			return new DoctrineSubscriptionRepository( $this->getConnection() );
		} );

		$pimple['comment_repository'] = $pimple->share( function() {
			return new DbalCommentRepository( $this->getEntityManager()->getRepository( Donation::class ) );
		} );

		$pimple['mail_validator'] = $pimple->share( function() {
			return new MailValidator( new InternetDomainNameValidator() );
		} );

		$pimple['request_validator'] = $pimple->share( function() {
			return new SubscriptionValidator( $this->getMailValidator() );
		} );

		$pimple['contact_validator'] = $pimple->share( function() {
			return new GetInTouchValidator( $this->getMailValidator() );
		} );

		$pimple['mw_api'] = $pimple->share( function() {
			return new MediawikiApi(
				$this->config['cms-wiki-api-url'],
				$this->getGuzzleClient()
			);
		} );

		$pimple['guzzle_client'] = $pimple->share( function() {
			$middlewareFactory = new MiddlewareFactory();
			$middlewareFactory->setLogger( $this->getLogger() );

			$handlerStack = HandlerStack::create( new CurlHandler() );
			$handlerStack->push( $middlewareFactory->retry() );

			return new Client( [
				'cookies' => true,
				'handler' => $handlerStack,
				'headers' => [ 'User-Agent' => 'WMDE Fundraising Frontend' ],
			] );
		} );

		$pimple['translator'] = $pimple->share( function() {
			$translationFactory = new TranslationFactory();
			$loaders = [
				'json' => $translationFactory->newJsonLoader()
			];
			$locale = 'de_DE'; // In the future, this could be configured somehow
			$translator = $translationFactory->create( $loaders, $locale );
			$translator->addResource( 'json', __DIR__ . '/../app/translations/messages.de_DE.json', $locale );
			$translator->addResource( 'json', __DIR__ . '/../app/translations/validations.de_DE.json', $locale, 'validations' );
			return $translator;
		} );

		$pimple['twig_factory'] = $pimple->share( function () {
			return new TwigFactory( $this->config['twig'] );
		} );

		$pimple['twig'] = $pimple->share( function( $pimple ) {
			$twigFactory = $this->getTwigFactory();
			$loaders = array_filter( [
				$twigFactory->newFileSystemLoader(),
				$twigFactory->newArrayLoader(), // This is just a fallback for testing
				$twigFactory->newWikiPageLoader( $this->newWikiContentProvider() ),
			] );
			$extensions = [
				$twigFactory->newStringLoaderExtension(),
				$twigFactory->newTranslationExtension( $this->getTranslator() )
			];
			return $twigFactory->create( $loaders, $extensions );
		} );

		$pimple['logger'] = $pimple->share( function() {
			$logger = new Logger( 'WMDE Fundraising Frontend logger' );

			$streamHandler = new StreamHandler( $this->newLoggerPath( ( new \DateTime() )->format( 'Y-m-d\TH:i:s\Z' ) ) );
			$bufferHandler = new BufferHandler( $streamHandler, 500, Logger::DEBUG, true, true );
			$streamHandler->setFormatter( new LineFormatter( "%message%\n" ) );
			$logger->pushHandler( $bufferHandler );

			$errorHandler = new StreamHandler( $this->newLoggerPath( 'error' ), Logger::ERROR );
			$errorHandler->setFormatter( new JsonFormatter() );
			$logger->pushHandler( $errorHandler );

			return $logger;
		} );

		$pimple['messenger'] = $pimple->share( function() {
			return new Messenger(
				new Swift_MailTransport(),
				$this->getOperatorAddress(),
				$this->config['operator-displayname']
			);
		} );

		return $pimple;
	}

	public function getConnection(): Connection {
		return $this->pimple['dbal_connection'];
	}

	public function getEntityManager(): EntityManager {
		return $this->pimple['entity_manager'];
	}

	public function newInstaller(): Installer {
		return ( new StoreFactory( $this->getConnection() ) )->newInstaller();
	}

	public function newValidateEmailUseCase(): ValidateEmailUseCase {
		return new ValidateEmailUseCase( $this->getMailValidator() );
	}

	public function newListCommentsUseCase(): ListCommentsUseCase {
		return new ListCommentsUseCase( $this->newCommentRepository() );
	}

	public function newCommentListJsonPresenter(): CommentListJsonPresenter {
		return new CommentListJsonPresenter();
	}

	public function newCommentListRssPresenter(): CommentListRssPresenter {
		return new CommentListRssPresenter( new TwigTemplate(
			$this->getTwig(),
			'CommentList.rss.twig'
		) );
	}

	public function newCommentListHtmlPresenter(): CommentListHtmlPresenter {
		return new CommentListHtmlPresenter( $this->getLayoutTemplate( 'CommentList.html.twig' ) );
	}

	private function newCommentRepository(): CommentRepository {
		return $this->pimple['comment_repository'];
	}

	private function newRequestRepository(): SubscriptionRepository {
		return $this->pimple['request_repository'];
	}

	public function setRequestRepository( SubscriptionRepository $requestRepository ) {
		$this->pimple['request_repository'] = $requestRepository;
	}

	private function getRequestValidator(): SubscriptionValidator {
		return $this->pimple['request_validator'];
	}

	private function getMailValidator(): MailValidator {
		return $this->pimple['mail_validator'];
	}

	public function newDisplayPageUseCase(): DisplayPageUseCase {
		return new DisplayPageUseCase(
			$this->newWikiContentProvider()
		);
	}

	public function newDisplayPagePresenter(): DisplayPagePresenter {
		return new DisplayPagePresenter( $this->getLayoutTemplate( 'DisplayPageLayout.twig' ) );
	}

	public function newAddSubscriptionHTMLPresenter(): AddSubscriptionHTMLPresenter {
		return new AddSubscriptionHTMLPresenter( $this->getLayoutTemplate( 'AddSubscription.twig' ) );
	}

	public function newAddSubscriptionJSONPresenter(): AddSubscriptionJSONPresenter {
		return new AddSubscriptionJSONPresenter( $this->getTranslator() );
	}

	public function newGetInTouchHTMLPresenter(): GetInTouchHTMLPresenter {
		return new GetInTouchHTMLPresenter( $this->getLayoutTemplate( 'GetInTouch.twig' ) );
	}

	public function getTwig(): Twig_Environment {
		return $this->pimple['twig'];
	}

	/**
	 * Get a template, with the content for the layout areas filled in.
	 *
	 * @param string $templateName
	 * @return TwigTemplate
	 */
	private function getLayoutTemplate( string $templateName ): TwigTemplate {
		 return new TwigTemplate(
			$this->getTwig(),
			$templateName,
			[
				'basepath' => $this->config['web-basepath'],
				'header_template' => $this->config['default-layout-templates']['header'],
				'footer_template' => $this->config['default-layout-templates']['footer'],
				'no_js_notice_template' => $this->config['default-layout-templates']['no-js-notice'],
			]
		);
	}

	private function newPageRetriever(): PageRetriever {
		return new ApiBasedPageRetriever(
			$this->getMediaWikiApi(),
			new ApiUser( $this->config['cms-wiki-user'], $this->config['cms-wiki-password'] ),
			$this->getLogger()
		);
	}

	private function getMediaWikiApi(): MediawikiApi {
		return $this->pimple['mw_api'];
	}

	public function setMediaWikiApi( MediawikiApi $api ) {
		$this->pimple['mw_api'] = $api;
	}

	private function getGuzzleClient(): ClientInterface {
		return $this->pimple['guzzle_client'];
	}

	private function newWikiContentProvider() {
		return new WikiContentProvider(
			$this->newPageRetriever(),
			$this->newPageContentModifier(),
			$this->config['cms-wiki-title-prefix']
		);
	}

	public function getLogger(): LoggerInterface {
		return $this->pimple['logger'];
	}

	private function newLoggerPath( string $fileName ): string {
		return __DIR__ . '/../var/log/' . $fileName . '.log';
	}

	private function newPageContentModifier(): PageContentModifier {
		return new PageContentModifier(
			$this->getLogger()
		);
	}

	public function newAddSubscriptionUseCase(): AddSubscriptionUseCase {
		return new AddSubscriptionUseCase(
			$this->newRequestRepository(),
			$this->getRequestValidator(),
			$this->newAddSubscriptionMailer()
		);
	}

	private function newAddSubscriptionMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate( $this->getTwig(), 'AddSubscriptionMailExternal.twig' ),
			$this->getTranslator()->trans( 'Your membership with Wikimedia Germany' )
		);
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

	public function setRequestValidator( SubscriptionValidator $requestValidator ) {
		$this->pimple['request_validator'] = $requestValidator;
	}

	public function setPageTitlePrefix( string $prefix ) {
		$this->config['cms-wiki-title-prefix'] = $prefix;
	}

	public function newGetInTouchUseCase() {
		return new GetInTouchUseCase(
			$this->getContactValidator(),
			$this->getMessenger(),
			$this->getContactConfirmationMessage()
		);
	}

	private function getContactValidator(): GetInTouchValidator {
		return $this->pimple['contact_validator'];
	}

	private function getMessenger(): Messenger {
		return $this->pimple['messenger'];
	}

	public function setMessenger( Messenger $messenger ) {
		$this->pimple['messenger'] = $messenger;
	}

	public function getOperatorAddress() {
		return new MailAddress( $this->config['operator-email'] );
	}

	public function newInternalErrorHTMLPresenter(): InternalErrorHTMLPresenter {
		return new InternalErrorHTMLPresenter( $this->getLayoutTemplate( 'Error.twig' ) );
	}

	private function getContactConfirmationMessage() {
		return new TemplatedMessage(
			$this->getTranslator()->trans( 'Your inquiry at Wikimedia' ),
			new TwigTemplate( $this->getTwig(), 'GetInTouchConfirmation.twig' )
		);
	}

	private function getTranslator(): TranslatorInterface {
		return $this->pimple['translator'];
	}

	private function getTwigFactory(): TwigFactory {
		return $this->pimple['twig_factory'];
	}

}
