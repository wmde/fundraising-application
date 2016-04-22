<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

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
use NumberFormatter;
use Psr\Log\LoggerInterface;
use Swift_MailTransport;
use Swift_NullTransport;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use Twig_Extensions_Extension_Intl;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineCommentRepository;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizer;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\DataAccess\UniqueTransferCodeGenerator;
use WMDE\Fundraising\Frontend\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Presentation\CreditCardConfig;
use WMDE\Fundraising\Frontend\Presentation\CreditCardUrlGenerator;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Presentation\PayPalConfig;
use WMDE\Fundraising\Frontend\Presentation\PayPalUrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormViolationPresenter;
use WMDE\Fundraising\Frontend\Domain\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineSubscriptionRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\Honorifics;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\SimpleTransferCodeGenerator;
use WMDE\Fundraising\Frontend\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\RandomTokenGenerator;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CancelDonationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListRssPresenter;
use WMDE\Fundraising\Frontend\Presentation\Content\WikiContentProvider;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CreditCardPaymentHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;
use WMDE\Fundraising\Frontend\Presentation\Presenters\GetInTouchHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationUseCase;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;
use WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase;
use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation\ShowDonationConfirmationUseCase;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\AllowedValuesValidator;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonalInfoValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionDuplicateValidator;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\DataAccess\ApiBasedPageRetriever;
use WMDE\Fundraising\Frontend\Infrastructure\PageRetriever;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DisplayPagePresenter;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\Frontend\UseCases\CheckIban\CheckIbanUseCase;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanUseCase;
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
			return new DoctrineSubscriptionRepository( $this->getEntityManager() );
		} );

		$pimple['donation_repository'] = $pimple->share( function() {
			return new DoctrineDonationRepository( $this->getEntityManager() );
		} );

		$pimple['membership_application_repository'] = $pimple->share( function() {
			return new DoctrineMembershipApplicationRepository( $this->getEntityManager() );
		} );

		$pimple['comment_repository'] = $pimple->share( function() {
			return new DoctrineCommentRepository(
				$this->getEntityManager()
			);
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
			$translator->addResource( 'json', __DIR__ . '/../../app/translations/messages.' . $locale . '.json', $locale );
			$translator->addResource(
				'json',
				__DIR__ . '/../../app/translations/paymentTypes.' . $locale . '.json',
				$locale,
				'paymentTypes'
			);
			$translator->addResource(
				'json',
				__DIR__ . '/../../app/translations/paymentIntervals.' . $locale . '.json',
				$locale,
				'paymentIntervals'
			);
			$translator->addResource( 'json', __DIR__ . '/../../app/translations/validations.' . $locale . '.json', $locale,
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
				$twigFactory->newTranslationExtension( $this->getTranslator() ),
				new Twig_Extensions_Extension_Intl()
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

		$pimple['confirmation-page-selector'] = $pimple->share( function() {
			return new DonationConfirmationPageSelector( $this->config['confirmation-pages'] );
		} );

		$pimple['paypal-payment-notification-verifier'] = $pimple->share( function() {
			return new PayPalPaymentNotificationVerifier(
				new Client(),
				$this->config['paypal']
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

	public function newListCommentsUseCase(): ListCommentsUseCase {
		return new ListCommentsUseCase( $this->getCommentFinder() );
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

	private function getCommentFinder(): CommentFinder {
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

	public function getMailValidator(): MailValidator {
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

	public function newAddSubscriptionHTMLPresenter(): AddSubscriptionHtmlPresenter {
		return new AddSubscriptionHtmlPresenter( $this->getLayoutTemplate( 'AddSubscription.twig' ), $this->getTranslator() );
	}

	public function newConfirmSubscriptionHtmlPresenter(): ConfirmSubscriptionHtmlPresenter {
		return new ConfirmSubscriptionHtmlPresenter(
			$this->getLayoutTemplate( 'ConfirmSubscription.html.twig' ),
			$this->getTranslator()
		);
	}

	public function newAddSubscriptionJSONPresenter(): AddSubscriptionJsonPresenter {
		return new AddSubscriptionJsonPresenter( $this->getTranslator() );
	}

	public function newGetInTouchHTMLPresenter(): GetInTouchHtmlPresenter {
		return new GetInTouchHtmlPresenter( $this->getLayoutTemplate( 'GetInTouch.twig' ), $this->getTranslator() );
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
			$this->getDefaultTwigVariables()
		);
	}

	/**
	 * Get a layouted template that includes another template
	 *
	 * @param string $templateName Template to include
	 * @return TwigTemplate
	 */
	private function getIncludeTemplate( string $templateName ): TwigTemplate {
		return new TwigTemplate(
			$this->getTwig(),
			'IncludeInLayout.twig',
			array_merge(
				$this->getDefaultTwigVariables(),
				[ 'main_template' => $templateName]
			)
		);
	}

	private function getDefaultTwigVariables() {
		return [
			'basepath' => $this->config['web-basepath'],
			'honorifics' => $this->getHonorifics()->getList(),
			'header_template' => $this->config['default-layout-templates']['header'],
			'footer_template' => $this->config['default-layout-templates']['footer'],
			'no_js_notice_template' => $this->config['default-layout-templates']['no-js-notice'],
		];
	}

	private function newReferrerGeneralizer() {
		return new ReferrerGeneralizer(
			$this->config['referrer-generalization']['default'],
			$this->config['referrer-generalization']['domain-map']
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
		return __DIR__ . '/../../var/log/' . $fileName . '.log';
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
			$this->getTranslator()->trans( 'mail_subject_membership' )
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
				$this->getTranslator()->trans( 'mail_subject_membership' )
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

	public function setSubscriptionValidator( SubscriptionValidator $subscriptionValidator ) {
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
			$this->getTranslator()->trans( 'mail_subject_getintouch' )
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

	private function newPaymentTypeValidator(): AllowedValuesValidator {
		return new AllowedValuesValidator( PaymentType::getPaymentTypes() );
	}

	private function newBankDataValidator(): BankDataValidator {
		return new BankDataValidator( new IbanValidator( $this->newBankDataConverter() ) );
	}

	private function getMessenger(): Messenger {
		return $this->pimple['messenger'];
	}

	public function setMessenger( Messenger $messenger ) {
		$this->pimple['messenger'] = $messenger;
	}

	public function setNullMessenger() {
		$this->setMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$this->getOperatorAddress()
		) );
	}

	public function getOperatorAddress() {
		return new EmailAddress( $this->config['operator-email'] );
	}

	public function newInternalErrorHTMLPresenter(): InternalErrorHtmlPresenter {
		return new InternalErrorHtmlPresenter( $this->getLayoutTemplate( 'Error.twig' ) );
	}

	public function getTranslator(): TranslatorInterface {
		return $this->pimple['translator'];
	}

	public function setTranslator( TranslatorInterface $translator ) {
		$this->pimple['translator'] = $translator;
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

	public function newCancelDonationUseCase( string $updateToken ): CancelDonationUseCase {
		return new CancelDonationUseCase(
			$this->getDonationRepository(),
			$this->newCancelDonationMailer(),
			$this->newDonationAuthorizer( $updateToken )
		);
	}

	private function newCancelDonationMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate(
				$this->getTwig(),
				'Mail_Donation_Cancellation_Confirmation.twig'
			),
			$this->getTranslator()->trans( 'mail_subject_confirm_cancellation' )
		);
	}

	public function newAddDonationUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->getDonationRepository(),
			$this->newDonationValidator(),
			$this->newReferrerGeneralizer(),
			$this->newAddDonationMailer(),
			$this->newBankTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenGenerator(),
			$this->newDonationAuthorizationUpdater()
		);
	}

	private function newBankTransferCodeGenerator(): TransferCodeGenerator {
		return new UniqueTransferCodeGenerator(
			new SimpleTransferCodeGenerator(),
			$this->getEntityManager()
		);
	}

	private function newDonationValidator(): DonationValidator {
		return new DonationValidator(
			$this->newAmountValidator(),
			new AmountPolicyValidator( 1000, 200, 300 ), // TODO: get from settings
			$this->newPersonalInfoValidator(),
			$this->getTextPolicyValidator( 'fields' ),
			$this->newPaymentTypeValidator(),
			$this->newBankDataValidator()
		);
	}

	public function newPersonalInfoValidator(): PersonalInfoValidator {
		return new PersonalInfoValidator(
			new PersonNameValidator(),
			new PhysicalAddressValidator(),
			$this->getMailValidator()
		);
	}

	private function newAddDonationMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate(
				$this->getTwig(),
				'Mail_Donation_Confirmation.twig', // TODO: ongoing unification of different templates
				[
					'basepath' => $this->config['web-basepath'],
					'greeting_generator' => $this->getGreetingGenerator()
				]
			),
			$this->getTranslator()->trans( 'mail_subject_confirm_donation' )
		);
	}

	public function newPayPalUrlGenerator() {
		return new PayPalUrlGenerator( $this->getPayPalConfig() );
	}

	private function getPayPalConfig() {
		return PayPalConfig::newFromConfig( $this->config['paypal'] );
	}

	private function newCreditCardUrlGenerator() {
		return new CreditCardUrlGenerator( $this->newCreditCardConfig() );
	}

	private function newCreditCardConfig() {
		return CreditCardConfig::newFromConfig( $this->config['creditcard'] );
	}

	public function getDonationRepository(): DonationRepository {
		return $this->pimple['donation_repository'];
	}

	public function newAmountValidator(): AmountValidator {
		return new AmountValidator(
			0.01,
			[
				PaymentType::CREDIT_CARD => 1,
				PaymentType::PAYPAL => 1,
			]
		);
	}

	public function newDecimalNumberFormatter(): NumberFormatter {
		return new NumberFormatter( $this->config['locale'], NumberFormatter::DECIMAL );
	}

	public function newAddCommentUseCase( string $updateToken ): AddCommentUseCase {
		return new AddCommentUseCase(
			$this->getCommentRepository(),
			$this->newDonationAuthorizer( $updateToken )
		);
	}

	private function newDonationAuthorizer( string $updateToken = null, string $accessToken = null ): DonationAuthorizer {
		return new DoctrineDonationAuthorizer(
			$this->getEntityManager(),
			$updateToken,
			$accessToken
		);
	}

	public function newDonationAuthorizationUpdater(): DonationAuthorizationUpdater {
		return new DoctrineDonationAuthorizationUpdater( $this->getEntityManager() );
	}

	private function getCommentRepository(): CommentRepository {
		return $this->pimple['comment_repository'];
	}

	public function setCommentRepository( CommentRepository $commentRepository ) {
		$this->pimple['comment_repository'] = $commentRepository;
	}

	public function newTokenGenerator(): TokenGenerator {
		return new RandomTokenGenerator(
			$this->config['token-length'],
			new \DateInterval( $this->config['token-validity-timestamp'] )
		);
	}

	public function newDonationConfirmationPresenter() {
		return new DonationConfirmationHtmlPresenter(
			$this->getLayoutTemplate( 'DonationConfirmation.twig' )
		);
	}

	public function newCreditCardPaymentHtmlPresenter() {
		return new CreditCardPaymentHtmlPresenter(
			$this->getLayoutTemplate( 'CreditCardPayment.html.twig' ),
			$this->getTranslator(),
			$this->newCreditCardUrlGenerator()
		);
	}

	public function newCancelDonationHtmlPresenter() {
		return new CancelDonationHtmlPresenter(
			$this->getIncludeTemplate( 'Donation_Cancellation_Confirmation.twig' )
		);
	}

	public function newApplyForMembershipUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase();
	}

	public function newCancelMembershipApplicationUseCase( string $updateToken ): CancelMembershipApplicationUseCase {
		return new CancelMembershipApplicationUseCase(
			$this->newMembershipApplicationAuthorizer( $updateToken ),
			$this->getMembershipApplicationRepository(),
			$this->newCancelMembershipApplicationMailer()
		);
	}

	private function newMembershipApplicationAuthorizer(
		string $updateToken = null, string $accessToken = null ): MembershipApplicationAuthorizer {

		return new DoctrineMembershipApplicationAuthorizer(
			$this->getEntityManager(),
			$updateToken,
			$accessToken
		);
	}

	public function getMembershipApplicationRepository(): MembershipApplicationRepository {
		return $this->pimple['membership_application_repository'];
	}

	private function newCancelMembershipApplicationMailer(): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$this->getMessenger(),
			new TwigTemplate(
				$this->getTwig(),
				'Mail_Membership_Application_Cancellation_Confirmation.twig' // TODO: create
			),
			$this->getTranslator()->trans( 'mail_subject_confirm_membership_application_cancellation' ) // TODO: create
		);
	}

	public function newShowDonationConfirmationUseCase( string $accessToken ): ShowDonationConfirmationUseCase {
		return new ShowDonationConfirmationUseCase(
			$this->newDonationAuthorizer( null, $accessToken ),
			$this->getDonationRepository()
		);
	}

	public function setDonationConfirmationPageSelector( DonationConfirmationPageSelector $selector ) {
		$this->pimple['confirmation-page-selector'] = $selector;
	}

	public function getDonationConfirmationPageSelector() {
		return $this->pimple['confirmation-page-selector'];
	}

	public function newDonationFormViolationPresenter() {
		return new DonationFormViolationPresenter( $this->getLayoutTemplate( 'DonationForm.html.twig' ) );
	}

	public function newHandlePayPalPaymentNotificationUseCase() {
		return new HandlePayPalPaymentNotificationUseCase();
	}

	public function getPayPalPaymentNotificationVerifier(): PayPalPaymentNotificationVerifier {
		return $this->pimple['paypal-payment-notification-verifier'];
	}

	public function setPayPalPaymentNotificationVerifier( PayPalPaymentNotificationVerifier $verifier ) {
		$this->pimple['paypal-payment-notification-verifier'] = $verifier;
	}

}
