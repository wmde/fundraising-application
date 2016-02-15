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
use Swift_MailTransport;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository;
use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;
use WMDE\Fundraising\Frontend\DataAccess\DbalSubscriptionRepository;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Honorifics;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListRssPresenter;
use WMDE\Fundraising\Frontend\Presentation\Content\WikiContentProvider;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHTMLPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJSONPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\GetInTouchHTMLPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHTMLPresenter;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationUseCase;
use WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\AllowedValuesValidator;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionDuplicateValidator;
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
use WMDE\Fundraising\Frontend\Validation\TemplateNameValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
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

		$pimple['subscription_repository'] = $pimple->share( function() {
			return new DbalSubscriptionRepository( $this->getEntityManager() );
		} );

		$pimple['comment_repository'] = $pimple->share( function() {
			return new DbalCommentRepository( $this->getEntityManager()->getRepository( Donation::class ) );
		} );

		$pimple['mail_validator'] = $pimple->share( function() {
			return new MailValidator( new InternetDomainNameValidator() );
		} );

		$pimple['subscription_validator'] = $pimple->share( function() {
			return new SubscriptionValidator(
				$this->getMailValidator(),
				$this->getTextPolicyValidator( 'fields' ),
				$this->newSubscriptionDuplicateValidator(),
				$this->newHonorificValidator()
			);
		} );

		$pimple['template_name_validator'] = $pimple->share( function() {
			return new TemplateNameValidator( $this->getTwig() );
		} );

		$pimple['contact_validator'] = $pimple->share( function() {
			return new GetInTouchValidator( $this->getMailValidator() );
		} );

		$pimple['greeting_generator'] = $pimple->share( function() {
			return new GreetingGenerator();
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
			$locale = $this->config['locale'];
			$translator = $translationFactory->create( $loaders, $locale );
			$translator->addResource( 'json', __DIR__ . '/../app/translations/messages.' . $locale . '.json', $locale );
			$translator->addResource( 'json', __DIR__ . '/../app/translations/validations.' . $locale . '.json', $locale,
				'validations' );
			return $translator;
		} );

		// In the future, this could be locale-specific or filled from a DB table
		$pimple['honorifics'] = $pimple->share( function() {
			return new Honorifics( [
				'' => 'Kein Titel',
				'Dr.' => 'Dr.',
				'Prof.' => 'Prof.',
				'Prof. Dr.' => 'Prof. Dr.'
			] );
		} );

		$pimple['twig_factory'] = $pimple->share( function () {
			return new TwigFactory( $this->config['twig'] );
		} );

		$pimple['twig'] = $pimple->share( function() {
			$twigFactory = $this->getTwigFactory();
			$loaders = array_filter( [
				$twigFactory->newFileSystemLoader(),
				$twigFactory->newArrayLoader(), // This is just a fallback for testing
				$twigFactory->newWikiPageLoader( $this->newWikiContentProvider() ),
			] );
			$extensions = [
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

	public function getSubscriptionRepository(): SubscriptionRepository {
		return $this->pimple['subscription_repository'];
	}

	public function setSubscriptionRepository( SubscriptionRepository $subscriptionRepository ) {
		$this->pimple['subscription_repository'] = $subscriptionRepository;
	}

	private function getSubscriptionValidator(): SubscriptionValidator {
		return $this->pimple['subscription_validator'];
	}

	private function getMailValidator(): MailValidator {
		return $this->pimple['mail_validator'];
	}

	private function getTemplateNameValidator(): TemplateNameValidator {
		return $this->pimple['template_name_validator'];
	}

	public function newDisplayPageUseCase(): DisplayPageUseCase {
		return new DisplayPageUseCase(
			$this->getTemplateNameValidator()
		);
	}

	public function newDisplayPagePresenter(): DisplayPagePresenter {
		return new DisplayPagePresenter( $this->getLayoutTemplate( 'DisplayPageLayout.twig' ) );
	}

	public function newAddSubscriptionHTMLPresenter(): AddSubscriptionHTMLPresenter {
		return new AddSubscriptionHTMLPresenter( $this->getLayoutTemplate( 'AddSubscription.twig' ) );
	}

	public function newConfirmSubscriptionHtmlPresenter(): ConfirmSubscriptionHtmlPresenter {
		return new ConfirmSubscriptionHtmlPresenter( $this->getLayoutTemplate( 'ConfirmSubscription.html.twig' ) );
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
				'honorifics' => $this->getHonorifics()->getList(),
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
			$this->getSubscriptionRepository(),
			$this->getSubscriptionValidator(),
			$this->newAddSubscriptionMailer()
		);
	}

	public function newConfirmSubscriptionUseCase(): ConfirmSubscriptionUseCase {
		return new ConfirmSubscriptionUseCase(
			$this->getSubscriptionRepository(),
			$this->newConfirmSubscriptionMailer()
		);
	}

	private function newAddSubscriptionMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate(
				$this->getTwig(),
				'Mail_Subscription_Request.twig',
				[
					'basepath' => $this->config['web-basepath'],
					'greeting_generator' => $this->getGreetingGenerator()
				]
			),
			$this->getTranslator()->trans( 'Your membership with Wikimedia Germany' )
		);
	}

	private function newConfirmSubscriptionMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
				$this->getMessenger(),
				new TwigTemplate(
						$this->getTwig(),
						'Mail_Subscription_Confirmation.twig',
						[ 'greeting_generator' => $this->getGreetingGenerator() ]
				),
				$this->getTranslator()->trans( 'Your membership with Wikimedia Germany' )
		);
	}

	public function getGreetingGenerator() {
		return $this->pimple['greeting_generator'];
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

	public function setSubscriptionValidator(SubscriptionValidator $subscriptionValidator ) {
		$this->pimple['subscription_validator'] = $subscriptionValidator;
	}

	public function setPageTitlePrefix( string $prefix ) {
		$this->config['cms-wiki-title-prefix'] = $prefix;
	}

	public function newGetInTouchUseCase() {
		return new GetInTouchUseCase(
			$this->getContactValidator(),
			$this->getMessenger(),
			$this->newContactConfirmationMailer()
		);
	}

	private function newContactConfirmationMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate( $this->getTwig(), 'GetInTouchConfirmation.twig' ),
			'Ihre Anfrage an Wikimedia'
		);
	}

	private function getContactValidator(): GetInTouchValidator {
		return $this->pimple['contact_validator'];
	}

	private function newSubscriptionDuplicateValidator(): SubscriptionDuplicateValidator {
		return new SubscriptionDuplicateValidator(
				$this->getSubscriptionRepository(),
				$this->newSubscriptionDuplicateCutoffDate()
		);
	}

	private function newSubscriptionDuplicateCutoffDate(): \DateTime {
		$cutoffDateTime = new \DateTime();
		$cutoffDateTime->sub( new \DateInterval( $this->config['subscription-interval'] ) );
		return $cutoffDateTime;
	}

	private function newHonorificValidator(): AllowedValuesValidator {
		return new AllowedValuesValidator( $this->getHonorifics()->getKeys() );
	}

	private function getHonorifics(): Honorifics {
		return $this->pimple['honorifics'];
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

	private function getTranslator(): TranslatorInterface {
		return $this->pimple['translator'];
	}

	private function getTwigFactory(): TwigFactory {
		return $this->pimple['twig_factory'];
	}

	private function getTextPolicyValidator( $policyName ) {
		$policyValidator = new TextPolicyValidator();
		$contentProvider = $this->newWikiContentProvider();
		$textPolicyConfig = $this->config['text-policies'][$policyName];
		$badWords = $this->loadWordsFromWiki( $contentProvider, $textPolicyConfig['badwords'] ?? '' );
		$whiteWords = $this->loadWordsFromWiki( $contentProvider, $textPolicyConfig['whitewords'] ?? '' );
		$policyValidator->addBadWordsFromArray( $badWords );
		$policyValidator->addWhiteWordsFromArray( $whiteWords );
		return $policyValidator;
	}

	private function loadWordsFromWiki( WikiContentProvider $contentProvider, string $pageName ): array {
		if ( $pageName === '' ) {
			return [ ];
		}
		$content = $contentProvider->getContent( $pageName, 'raw' );
		$words = array_map( 'trim', explode( "\n", $content ) );

		return array_filter( $words );
	}

	public function newCancelDonationUseCase(): CancelDonationUseCase {
		return new CancelDonationUseCase();
	}

	public function newAddDonationUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->newDonationRepository(),
			new DonationValidator(
				new AmountValidator( 1 ), // TODO: get from settings
				new AmountPolicyValidator( 1000, 200, 300 ), // TODO: get from settings
				$this->getTextPolicyValidator( 'fields' ),
				new PersonNameValidator(),
				new PhysicalAddressValidator(),
				$this->getMailValidator()
			)
		);
	}

	private function newDonationRepository(): DonationRepository {
		return $this->pimple['donation_repository'];
	}

	public function setDonationRepository( DonationRepository $donationRepository ) {
		$this->pimple['donation_repository'] = $donationRepository;
	}

}
