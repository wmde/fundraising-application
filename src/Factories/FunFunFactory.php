<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use FileFetcher\ErrorLoggingFileFetcher;
use FileFetcher\SimpleFileFetcher;
use GuzzleHttp\Client;
use Locale;
use NumberFormatter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use RemotelyLiving\Doorkeeper\Doorkeeper;
use RemotelyLiving\Doorkeeper\Features\Set;
use RemotelyLiving\Doorkeeper\Requestor;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Twig\Environment;
use WMDE\Clock\SystemClock;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Euro\Euro;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineAddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressUseCase;
use WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\ReadAddressChangeUseCase;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\DonationContext\Authorization\TokenGenerator;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineCommentFinder;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationAuthorizer;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationEventLogger;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationTokenFetcher;
use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\DonationContext\DonationAcceptedEventHandler;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingCommentFinder;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingDonationRepository;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\DonationContext\Services\PaymentBookingService;
use WMDE\Fundraising\DonationContext\Services\PaymentBookingServiceWithUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\CreatePaymentWithUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\ModerationService;
use WMDE\Fundraising\DonationContext\UseCases\CancelDonation\CancelDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\CreditCardPaymentNotification\CreditCardNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentCompletionNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorValidator;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess\DoctrineLocationRepository;
use WMDE\Fundraising\Frontend\Autocomplete\UseCases\FindCitiesUseCase;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignBuilder;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignFeatureBuilder;
use WMDE\Fundraising\Frontend\BucketTesting\DataAccess\DoctrineBucketLogRepository;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\DoorkeeperFeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BestEffortBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\RandomBucketSelection;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\CreateAddressChangeHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DonationEventEmitter;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\MembershipEventEmitter;
use WMDE\Fundraising\Frontend\Infrastructure\JsonStringReader;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingTemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\GetInTouchMailerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailSubjectRendererInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailTemplateFilenameTraversable;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\OperatorMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\SubmissionRateLimit;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\JsonTranslator;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Infrastructure\TranslationsCollector;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\UserDataKeyGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;
use WMDE\Fundraising\Frontend\Presentation\BucketRenderer;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
use WMDE\Fundraising\Frontend\Presentation\Honorifics;
use WMDE\Fundraising\Frontend\Presentation\PaymentTypesSettings;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CancelDonationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CancelMembershipApplicationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListRssPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CreditCardNotificationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormViolationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonorUpdateHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ErrorPageHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ExceptionHtmlPresenterInterface;
use WMDE\Fundraising\Frontend\Presentation\Presenters\GetInTouchHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationFormPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipFormViolationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\PageNotFoundPresenter;
use WMDE\Fundraising\Frontend\Presentation\Salutations;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipTokenGenerator;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationAuthorizer;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationTokenFetcher;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineIncentiveFinder;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineMembershipApplicationEventLogger;
use WMDE\Fundraising\MembershipContext\DataAccess\IncentiveFinder;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\MembershipContext\Infrastructure\LoggingApplicationRepository;
use WMDE\Fundraising\MembershipContext\Infrastructure\MembershipApplicationEventLogger;
use WMDE\Fundraising\MembershipContext\Infrastructure\MembershipConfirmationMailer;
use WMDE\Fundraising\MembershipContext\Infrastructure\MembershipNotifier;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationTracker;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipPolicyValidator;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationUseCase;
use WMDE\Fundraising\PaymentContext\DataAccess\DoctrinePaymentIDRepository;
use WMDE\Fundraising\PaymentContext\DataAccess\DoctrinePaymentRepository;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\SofortClient;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\SofortLibClient;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\IbanBlockList;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentRepository;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\CreditCardConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PaymentURLFactory;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPalConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\SofortConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentValidator;
use WMDE\Fundraising\PaymentContext\Domain\Repositories\PaymentIDRepository;
use WMDE\Fundraising\PaymentContext\PaymentContextFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\ExternalVerificationServiceFactory;
use WMDE\Fundraising\PaymentContext\Services\KontoCheck\KontoCheckBankDataGenerator;
use WMDE\Fundraising\PaymentContext\Services\KontoCheck\KontoCheckIbanValidator;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\CharacterPickerPaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\RandomCharacterIndexGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\UniquePaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Services\TransactionIdFinder\DoctrineTransactionIdFinder;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\BookPaymentUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\CancelPayment\CancelPaymentUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\CreatePayment\CreatePaymentUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateBankData\GenerateBankDataFromGermanLegacyBankDataUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\GetPayment\GetPaymentUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\ValidateIban\ValidateIbanUseCase;
use WMDE\Fundraising\SubscriptionContext\DataAccess\DoctrineSubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\LoggingSubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;
use WMDE\Fundraising\SubscriptionContext\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\SubscriptionContext\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase;
use WMDE\Fundraising\SubscriptionContext\Validation\SubscriptionDuplicateValidator;
use WMDE\Fundraising\SubscriptionContext\Validation\SubscriptionValidator;
use WMDE\FunValidators\DomainNameValidator;
use WMDE\FunValidators\Validators\AddressValidator;
use WMDE\FunValidators\Validators\AllowedValuesValidator;
use WMDE\FunValidators\Validators\AmountPolicyValidator;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\TextPolicyValidator;

/**
 * @license GPL-2.0-or-later
 */
class FunFunFactory implements LoggerAwareInterface {

	public const DONATION_RATE_LIMIT_SESSION_KEY = 'donation_timestamp';
	public const MEMBERSHIP_RATE_LIMIT_SESSION_KEY = 'memapp_timestamp';

	/**
	 * Nested configuration values.
	 *
	 * See JSON schema in app/config/schema.json for allowed values
	 *
	 * @var array
	 */
	private array $config;

	private bool $addDoctrineSubscribers = true;

	/**
	 * Holds instances that should only be initialized once.
	 *
	 * Type is classname_string => instance
	 *
	 * If there are multiple instances of a class (e.g. loggers), the class names must get a suffix, separated with "::"
	 *
	 * @var array
	 */
	private array $sharedObjects;

	public function __construct( array $config ) {
		$this->config = $config;
		$this->sharedObjects = [];
	}

	private function createSharedObject( string $id, callable $constructionFunction ) { // @codingStandardsIgnoreLine
		if ( !isset( $this->sharedObjects[$id] ) ) {
			$this->sharedObjects[$id] = $constructionFunction();
		}
		return $this->sharedObjects[$id];
	}

	public function getConnection(): Connection {
		return $this->createSharedObject( DriverManager::class, function () {
			return DriverManager::getConnection( $this->config['db'] );
		} );
	}

	public function getEntityManager(): EntityManager {
		$factory = $this->getDoctrineFactory();
		$entityManager = $this->getPlainEntityManager();

		if ( $this->addDoctrineSubscribers ) {
			$factory->setupEventSubscribers(
				$entityManager->getEventManager(),
				// if we have custom Doctrine event subscribers, add them here
			);
		}

		return $entityManager;
	}

	/**
	 * Returns an EntityManager without
	 * @return EntityManager
	 */
	public function getPlainEntityManager(): EntityManager {
		return $this->createSharedObject( EntityManager::class, function () {
			return $this->getDoctrineFactory()->getEntityManager();
		} );
	}

	private function getDoctrineFactory(): DoctrineFactory {
		return $this->createSharedObject( DoctrineFactory::class, function () {
			$donationContextFactory = $this->getDonationContextFactory();
			$membershipContextFactory = $this->getMembershipContextFactory();
			$paymentContextFactory = $this->getPaymentContextFactory();
			$subscriptionContextFactory = new SubscriptionContextFactory();
			$addressChangeContextFactory = new AddressChangeContextFactory();
			$bucketTestingContextFactory = new BucketTestingContextFactory();
			$autocompleteContextFactory = new AutocompleteContextFactory();
			return new DoctrineFactory(
				$this->getConnection(),
				$this->getDoctrineConfiguration(),
				$donationContextFactory,
				$membershipContextFactory,
				$paymentContextFactory,
				$subscriptionContextFactory,
				$addressChangeContextFactory,
				$bucketTestingContextFactory,
				$autocompleteContextFactory
			);
		} );
	}

	private function newDonationEventLogger(): DonationEventLogger {
		return new BestEffortDonationEventLogger(
			new DoctrineDonationEventLogger( $this->getEntityManager() ),
			$this->getLogger()
		);
	}

	public function newListCommentsUseCase(): ListCommentsUseCase {
		return new ListCommentsUseCase( $this->getCommentFinder() );
	}

	public function newCommentListJsonPresenter(): CommentListJsonPresenter {
		return new CommentListJsonPresenter();
	}

	public function newCommentListRssPresenter(): CommentListRssPresenter {
		return new CommentListRssPresenter( new TwigTemplate(
			$this->getSkinTwig(),
			'Comment_List.rss.twig'
		) );
	}

	public function newCommentListHtmlPresenter(): CommentListHtmlPresenter {
		return new CommentListHtmlPresenter( $this->getLayoutTemplate( 'Comment_List.html.twig' ) );
	}

	private function getCommentFinder(): CommentFinder {
		return $this->createSharedObject( CommentFinder::class, function () {
			return new LoggingCommentFinder(
				new DoctrineCommentFinder( $this->getEntityManager() ),
				$this->getLogger()
			);
		} );
	}

	public function getSubscriptionRepository(): SubscriptionRepository {
		return $this->createSharedObject( SubscriptionRepository::class, function () {
			return new LoggingSubscriptionRepository(
				new DoctrineSubscriptionRepository( $this->getEntityManager() ),
				$this->getLogger()
			);
		} );
	}

	public function setSubscriptionRepository( SubscriptionRepository $subscriptionRepository ): void {
		$this->sharedObjects[SubscriptionRepository::class] = $subscriptionRepository;
	}

	private function getSubscriptionValidator(): SubscriptionValidator {
		return $this->createSharedObject( SubscriptionValidator::class, function (): SubscriptionValidator {
			return new SubscriptionValidator(
				$this->getEmailValidator(),
				$this->newSubscriptionDuplicateValidator(),
			);
		} );
	}

	public function getEmailValidator(): EmailValidator {
		return $this->createSharedObject( EmailValidator::class, function () {
			return new EmailValidator( $this->getDomainNameValidator() );
		} );
	}

	private function getDomainNameValidator(): DomainNameValidator {
		return $this->createSharedObject( DomainNameValidator::class, static function () {
			return new InternetDomainNameValidator();
		} );
	}

	public function setDomainNameValidator( DomainNameValidator $validator ): void {
		$this->sharedObjects[DomainNameValidator::class] = $validator;
	}

	public function newAddSubscriptionHtmlPresenter(): AddSubscriptionHtmlPresenter {
		return new AddSubscriptionHtmlPresenter( $this->getLayoutTemplate( 'Subscription_Form.html.twig' ) );
	}

	public function newConfirmSubscriptionHtmlPresenter(): ConfirmSubscriptionHtmlPresenter {
		return new ConfirmSubscriptionHtmlPresenter(
			$this->getLayoutTemplate( 'Confirm_Subscription.twig' )
		);
	}

	public function newAddSubscriptionJsonPresenter(): AddSubscriptionJsonPresenter {
		return new AddSubscriptionJsonPresenter();
	}

	public function newGetInTouchHtmlPresenter(): GetInTouchHtmlPresenter {
		return new GetInTouchHtmlPresenter(
			$this->getLayoutTemplate( 'Contact_Form.html.twig' ),
			$this->getGetInTouchCategories()
		);
	}

	public function getGetInTouchCategories(): array {
		$json = ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/contact_categories.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
		return json_decode( $json, true );
	}

	public function getApplicationOfFundsContent(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/use_of_funds_content.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function getApplicationOfFundsMessages(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/messages/useOfFundsMessages.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function getFaqContent(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/faq.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function getSupportersList(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/supporters.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function setSkinTwigEnvironment( Environment $twig ): void {
		$this->sharedObjects[Environment::class . '::Skin'] = $twig;
	}

	public function getSkinTwig(): Environment {
		return $this->createSharedObject( Environment::class . '::Skin', function (): Environment {
			$config = $this->config['twig'];
			$config['loaders']['filesystem']['template-dir'] = $this->getSkinDirectory();
			$packageFactory = new AssetPackageFactory( $this->getApplicationEnvironment(), $this->config['assets-path'], $this->getRootPath() );
			$factory = new WebTemplatingFactory(
				$config,
				$this->getCachePath() . '/twig',
				$this->getTranslationCollector()->collectTranslations(),
				$this->getContentProvider(),
				$packageFactory->newAssetPackages()
			);
			return $factory->newTemplatingEnvironment(
				[
					'basepath' => $this->config['web-basepath'],
					'assets_path' => $this->config['assets-path'],
					'application_environment' => $this->getApplicationEnvironment(),
				]
			);
		} );
	}

	public function getMailerTwig(): Environment {
		return $this->createSharedObject( Environment::class . '::Mailer', function (): Environment {
			$config = $this->config['mailer-twig'];
			$config['loaders']['filesystem']['template-dir'] = $this->getMailTemplateDirectory();
			$factory = new MailerTemplatingFactory(
				$config,
				$this->getCachePath() . '/twig',
				$this->getMailTranslator(),
				$this->getContentProvider(),
				$this->getUrlGenerator(),
			);
			$locale = Locale::parseLocale( $this->getLocale() );
			return $factory->newTemplatingEnvironment(
				$this->getDayOfWeekName(),
				$locale['language']
			);
		} );
	}

	/**
	 * Get a template, with the content for the layout areas filled in.
	 *
	 * @param string $templateName
	 * @param array $context Additional variables for the template
	 * @return TwigTemplate
	 */
	public function getLayoutTemplate( string $templateName, array $context = [] ): TwigTemplate {
		 return new TwigTemplate(
			$this->getSkinTwig(),
			$templateName,
			array_merge( $this->getDefaultTwigVariables(), $context )
		);
	}

	public function getMailerTemplate( string $templateName, array $context = [] ): TwigTemplate {
		return new TwigTemplate(
			$this->getMailerTwig(),
			$templateName,
			array_merge( $this->getDefaultTwigVariables(), $context )
		);
	}

	private function getDefaultTwigVariables(): array {
		return [
			'honorifics' => $this->getHonorifics()->getList(),
			'salutations' => $this->getSalutations()->getList(),
			'piwik' => $this->config['piwik'],
			'site_metadata' => $this->getSiteMetaData(),
			'selectedBuckets' => BucketRenderer::renderBuckets( ...$this->getSelectedBuckets() ),
		];
	}

	public function getLogger(): LoggerInterface {
		return $this->createSharedObject( LoggerInterface::class . '::Application', static function () {
			return new NullLogger();
		} );
	}

	public function getPaypalLogger(): LoggerInterface {
		return $this->createSharedObject( LoggerInterface::class . '::Paypal', static function () {
			return new NullLogger();
		} );
	}

	public function getSofortLogger(): LoggerInterface {
		return $this->createSharedObject( LoggerInterface::class . '::Sofort', static function () {
			return new NullLogger();
		} );
	}

	public function getWritableApplicationDataPath(): string {
		return __DIR__ . '/../../var';
	}

	public function getCachePath(): string {
		return $this->getWritableApplicationDataPath() . '/cache';
	}

	public function getLoggingPath(): string {
		return $this->getWritableApplicationDataPath() . '/log';
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

	private function newAddSubscriptionMailer(): SubscriptionTemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSubOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Subscription_Request.txt.twig',
				[
					'greeting_generator' => $this->getGreetingGenerator()
				]
			),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_subscription' )
		);
	}

	private function newConfirmSubscriptionMailer(): SubscriptionTemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSubOrganizationMessenger(),
			new TwigTemplate(
					$this->getMailerTwig(),
					'Subscription_Confirmation.txt.twig',
					[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_subscription_confirmed' )
		);
	}

	private function newErrorHandlingTemplateMailer( Messenger $messenger, TwigTemplate $template, MailSubjectRendererInterface $subjectRenderer ): ErrorHandlingTemplateBasedMailer {
		return new ErrorHandlingTemplateBasedMailer(
			$this->newTemplateMailer( $messenger, $template, $subjectRenderer ),
			$this->getLogger()
		);
	}

	private function newTemplateMailer( Messenger $messenger, TwigTemplate $template, MailSubjectRendererInterface $subjectRenderer ): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$messenger,
			$template,
			$subjectRenderer
		);
	}

	public function getGreetingGenerator(): GreetingGenerator {
		return $this->createSharedObject( GreetingGenerator::class, function (): GreetingGenerator {
			return new GreetingGenerator( $this->getMailTranslator(), $this->getSalutations(), 'mail_introduction_generic' );
		} );
	}

	public function newCheckIbanUseCase(): ValidateIbanUseCase {
		return new ValidateIbanUseCase(
			$this->newIbanBlockList(),
			$this->newBankDataConverter()
		);
	}

	public function newGenerateBankDataFromGermanLegacyBankDataUseCase(): GenerateBankDataFromGermanLegacyBankDataUseCase {
		return new GenerateBankDataFromGermanLegacyBankDataUseCase(
			$this->newBankDataConverter(),
			$this->newIbanBlockList()
		);
	}

	public function newIbanPresenter(): IbanPresenter {
		return new IbanPresenter();
	}

	public function newBankDataConverter(): BankDataGenerator {
		return new KontoCheckBankDataGenerator( $this->newIbanValidator() );
	}

	public function setSubscriptionValidator( SubscriptionValidator $subscriptionValidator ): void {
		$this->sharedObjects[SubscriptionValidator::class] = $subscriptionValidator;
	}

	public function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->getContactValidator(),
			$this->newContactOperatorMailer(),
			$this->newContactUserMailer()
		);
	}

	private function newContactUserMailer(): GetInTouchMailerInterface {
		return $this->newTemplateMailer(
			$this->getSubOrganizationMessenger(),
			new TwigTemplate( $this->getMailerTwig(), 'Contact_Confirm_to_User.txt.twig' ),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_getintouch' )
		);
	}

	private function newContactOperatorMailer(): OperatorMailer {
		return new OperatorMailer(
			$this->getSubOrganizationMessenger(),
			new TwigTemplate( $this->getMailerTwig(), 'Contact_Forward_to_Operator.txt.twig' )
		);
	}

	private function getContactValidator(): GetInTouchValidator {
		return $this->createSharedObject( GetInTouchValidator::class, function () {
			return new GetInTouchValidator( $this->getEmailValidator() );
		} );
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

	public function newGetPaymentUseCase(): GetPaymentUseCase {
		return new GetPaymentUseCase(
			new DoctrinePaymentRepository( $this->getEntityManager() ),
			new KontoCheckBankDataGenerator( $this->newIbanValidator() ),
			$this->newDoctrineTransactionIdFinder()
		);
	}

	private function newHonorificValidator(): AllowedValuesValidator {
		return new AllowedValuesValidator( $this->getHonorifics()->getKeys() );
	}

	private function getHonorifics(): Honorifics {
		return $this->createSharedObject( Honorifics::class,
			function () {
				$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/honorifics.json' );
				$honorificsData = json_decode( $json, true, 16, JSON_THROW_ON_ERROR );
				return new Honorifics( $honorificsData );
			} );
	}

	private function getSalutations(): Salutations {
		return $this->createSharedObject( Salutations::class,
			function () {
				$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/salutations.json' );
				$data = json_decode( $json, true, 16, JSON_THROW_ON_ERROR );
				return new Salutations( $data[ 'salutations' ] );
			} );
	}

	private function getSubOrganizationMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'suborganization', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				$this->getSubOrganizationEmailAddress(),
				$this->config['contact-info']['suborganization']['name']
			);
		} );
	}

	public function setSubOrganizationMessenger( Messenger $messenger ): void {
		$this->sharedObjects[Messenger::class . 'suborganization'] = $messenger;
	}

	private function getOrganizationMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'organization', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				$this->getOrganizationEmailAddress(),
				$this->config['contact-info']['organization']['name']
			);
		} );
	}

	public function setOrganizationMessenger( Messenger $messenger ): void {
		$this->sharedObjects[Messenger::class . 'organization'] = $messenger;
	}

	private function getMailer(): MailerInterface {
		return $this->createSharedObject( MailerInterface::class, function (): MailerInterface {
			$transport = new EsmtpTransport(
				$this->config['smtp']['host'],
				$this->config['smtp']['port'],
			);
			$transport->setUsername( $this->config['smtp']['username'] )
				->setPassword( $this->config['smtp']['password'] );
			return new Mailer( $transport );
		} );
	}

	public function setNullMessenger(): void {
		$this->setSubOrganizationMessenger( new Messenger(
			new Mailer( new NullTransport() ),
			$this->getSubOrganizationEmailAddress()
		) );
		$this->setOrganizationMessenger( new Messenger(
			new Mailer( new NullTransport() ),
			$this->getOrganizationEmailAddress()
		) );
	}

	public function getSubOrganizationEmailAddress(): EmailAddress {
		return new EmailAddress( $this->config['contact-info']['suborganization']['email'] );
	}

	public function getOrganizationEmailAddress(): EmailAddress {
		return new EmailAddress( $this->config['contact-info']['organization']['email'] );
	}

	/**
	 * The ErrorPageHtmlPresenter presents specific messages for code branches that do explicit error handling
	 *
	 * @return ErrorPageHtmlPresenter
	 */
	public function newErrorPageHtmlPresenter(): ErrorPageHtmlPresenter {
		return new ErrorPageHtmlPresenter( $this->getLayoutTemplate( 'Error_Page.html.twig' ) );
	}

	/**
	 * The ExceptionHtmlPresenterInterface shows error pages when code threw an exception.
	 *
	 * It has different implementations for production and development. In development, it shows detailed information
	 * about the error. The production implementation shows a generic error message.
	 *
	 * @return ExceptionHtmlPresenterInterface
	 */
	public function getInternalErrorHtmlPresenter(): ExceptionHtmlPresenterInterface {
		$presenter = $this->createSharedObject( ExceptionHtmlPresenterInterface::class, static function (): ExceptionHtmlPresenterInterface {
			return new InternalErrorHtmlPresenter();
		} );
		// Don't use $this->getLayoutTemplate or $this->getDefaultTwigVariables() because those methods need
		// initialized buckets, which are not ready if the exception occurred early in the request cycle.
		$presenter->setTemplate( new TwigTemplate(
			$this->getSkinTwig(),
			'Error_Page.html.twig',
			[
				'piwik' => $this->config['piwik'],
				'site_metadata' => $this->getSiteMetaData(),
			]
		) );
		return $presenter;
	}

	public function setInternalErrorHtmlPresenter( ExceptionHtmlPresenterInterface $presenter ): void {
		$this->sharedObjects[ExceptionHtmlPresenterInterface::class] = $presenter;
	}

	public function newAccessDeniedHtmlPresenter(): ErrorPageHtmlPresenter {
		return new ErrorPageHtmlPresenter( $this->getLayoutTemplate( 'Access_Denied.twig' ) );
	}

	private function newTextPolicyValidator( string $policyName ): TextPolicyValidator {
		$fetcher = new ErrorLoggingFileFetcher(
			new SimpleFileFetcher(),
			$this->getLogger()
		);
		$textPolicyConfig = $this->config['text-policies'][$policyName];
		return new TextPolicyValidator(
			new WordListFileReader(
				$fetcher,
				$textPolicyConfig['badwords'] ? $this->getAbsolutePath( $textPolicyConfig['badwords'] ) : ''
			),
			new WordListFileReader(
				$fetcher,
				$textPolicyConfig['whitewords'] ? $this->getAbsolutePath( $textPolicyConfig['whitewords'] ) : ''
			)
		);
	}

	private function newCommentPolicyValidator(): TextPolicyValidator {
		return $this->newTextPolicyValidator( 'comment' );
	}

	public function newCancelDonationUseCase( string $updateToken ): CancelDonationUseCase {
		return new CancelDonationUseCase(
			$this->getDonationRepository(),
			$this->newCancelDonationMailer(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationEventLogger(),
			$this->newCancelPaymentUseCase()
		);
	}

	private function newCancelDonationMailer(): DonationTemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSubOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Donation_Cancellation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_confirm_cancellation' )
		);
	}

	public function newAddDonationUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->getDonationRepository(),
			$this->newDonationValidator(),
			$this->newDonationModerationService(),
			$this->newDonationConfirmationMailer(),
			$this->newDonationTokenFetcher(),
			$this->getDonationEventEmitter(),
			new CreatePaymentWithUseCase( $this->newCreatePaymentUseCase() )
		);
	}

	public function newCreatePaymentUseCase(): CreatePaymentUseCase {
		return new CreatePaymentUseCase(
			$this->newPaymentIDRepository(),
			$this->newPaymentRepository(),
			$this->newPaymentReferenceCodeGenerator(),
			new PaymentValidator(),
			$this->newCheckIbanUseCase(),
			new PaymentURLFactory(
				$this->newCreditCardUrlConfig(),
				$this->getPayPalUrlConfigForDonations(),
				$this->getSofortConfigForDonations(),
				$this->getSofortClient()
			)
		);
	}

	private function newPaymentReferenceCodeGenerator(): PaymentReferenceCodeGenerator {
		return new UniquePaymentReferenceCodeGenerator(
			new CharacterPickerPaymentReferenceCodeGenerator( new RandomCharacterIndexGenerator() ),
			$this->getEntityManager()
		);
	}

	private function newDonationValidator(): AddDonationValidator {
		return new AddDonationValidator(
			$this->getEmailValidator(),
			$this->newAddressValidator()
		);
	}

	public function newAddressValidator(): AddressValidator {
		$countries = $this->getCountries();
		$validation = $this->getValidationRules();

		$postcodeValidation = [];
		foreach ( $countries as $country ) {
			$postcodeValidation[$country->countryCode] = "/{$country->postCodeValidation}/";
		}

		$addressValidation = [];
		foreach ( $validation->address as $key => $pattern ) {
			$addressValidation[$key] = "/{$pattern}/";
		}

		return new AddressValidator( $postcodeValidation, $addressValidation );
	}

	public function newUpdateDonorUseCase( string $updateToken, string $accessToken ): UpdateDonorUseCase {
		return new UpdateDonorUseCase(
			$this->newDonationAuthorizer( $updateToken, $accessToken ),
			$this->newUpdateDonorValidator(),
			$this->getDonationRepository(),
			$this->newDonationConfirmationMailer(),
			$this->getDonationEventEmitter()
		);
	}

	private function newUpdateDonorValidator(): UpdateDonorValidator {
		return new UpdateDonorValidator( $this->newAddressValidator(), $this->getEmailValidator() );
	}

	private function newDonationConfirmationMailer(): DonationConfirmationMailer {
		return new DonationConfirmationMailer(
			$this->newErrorHandlingTemplateMailer(
				$this->getSubOrganizationMessenger(),
				new TwigTemplate(
					$this->getMailerTwig(),
					'Donation_Confirmation.txt.twig',
					[
						'greeting_generator' => $this->getGreetingGenerator()
					]
				),
				new DonationConfirmationMailSubjectRenderer(
					$this->getMailTranslator(),
					'mail_subject_confirm_donation',
					'mail_subject_confirm_donation_promise'
				)
			),
			$this->newGetPaymentUseCase()
		);
	}

	private function getPayPalUrlConfigForDonations(): PayPalConfig {
		return PayPalConfig::newFromConfig(
			array_merge( $this->config['paypal-donation'], [ 'locale' => $this->getLocale() ] ),
			new TranslatablePaymentItemDescription( 'paypal_item_name_donation', $this->getPaymentProviderItemsTranslator() )
		);
	}

	private function getSofortConfigForDonations(): SofortConfig {
		$config = $this->config['sofort'];
		$locale = \Locale::parseLocale( $this->getLocale() );
		$translator = $this->getPaymentProviderItemsTranslator();
		return new SofortConfig(
			strtoupper( $locale['language'] ),
			$config['return-url'],
			$config['cancel-url'],
			$config['notification-url'],
			new TranslatablePaymentItemDescription( 'sofort_item_name_donation', $translator )
		);
	}

	public function setSofortClient( SofortClient $client ): void {
		$this->sharedObjects[SofortClient::class] = $client;
	}

	private function getSofortClient(): SofortClient {
		return $this->createSharedObject( SofortClient::class, function () {
			$config = $this->config['sofort'];
			return new SofortLibClient( $config['config-key'] );
		} );
	}

	private function newCreditCardUrlConfig(): CreditCardConfig {
		$locale = \Locale::parseLocale( $this->getLocale() );
		return CreditCardConfig::newFromConfig(
			array_merge( $this->config['creditcard'], [ 'locale' => $locale['language'] ] ),
			new TranslatablePaymentItemDescription( 'credit_card_item_name_donation', $this->getPaymentProviderItemsTranslator() )
		);
	}

	public function getDonationRepository(): DonationRepository {
		return $this->createSharedObject( DonationRepository::class, function () {
			return new LoggingDonationRepository(
				new DoctrineDonationRepository(
					$this->getEntityManager(),
					$this->newGetPaymentUseCase()
				),
				$this->getLogger()
			);
		} );
	}

	public function newPaymentIDRepository(): PaymentIDRepository {
		return $this->createSharedObject( PaymentIDRepository::class, function () {
			return new DoctrinePaymentIDRepository(
				$this->getEntityManager()
			);
		} );
	}

	public function newPaymentRepository(): PaymentRepository {
		return $this->createSharedObject( PaymentRepository::class, function () {
			return new DoctrinePaymentRepository(
				$this->getEntityManager()
			);
		} );
	}

	public function getAddressChangeRepository(): AddressChangeRepository {
		return $this->createSharedObject( AddressChangeRepository::class, function () {
			return new DoctrineAddressChangeRepository( $this->getEntityManager() );
		} );
	}

	public function newChangeAddressUseCase(): ChangeAddressUseCase {
		return new ChangeAddressUseCase( $this->getAddressChangeRepository() );
	}

	public function newReadAddressChangeUseCase(): ReadAddressChangeUseCase {
		return new ReadAddressChangeUseCase( $this->getAddressChangeRepository() );
	}

	public function newPaymentValidator(): PaymentValidator {
		return new PaymentValidator();
	}

	public function newDecimalNumberFormatter(): NumberFormatter {
		return new NumberFormatter( $this->getLocale(), NumberFormatter::DECIMAL );
	}

	public function newAddCommentUseCase( string $updateToken ): AddCommentUseCase {
		return new AddCommentUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newCommentPolicyValidator(),
			$this->newAddCommentValidator()
		);
	}

	private function newDonationAuthorizer( string $updateToken = '', string $accessToken = '' ): DonationAuthorizer {
		return new DoctrineDonationAuthorizer(
			$this->getEntityManager(),
			$updateToken,
			$accessToken
		);
	}

	public function newDonationConfirmationPresenter(): DonationConfirmationHtmlPresenter {
		return new DonationConfirmationHtmlPresenter(
			new TwigTemplate(
				$this->getSkinTwig(), 'Donation_Confirmation.html.twig',
				array_merge(
					$this->getDefaultTwigVariables(),
					[
						'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForMembershipApplication(),
					]
				)
			),
			$this->getUrlGenerator(),
			$this->getCountries(),
			$this->getValidationRules()->address,
		);
	}

	public function newDonorUpdatePresenter(): DonorUpdateHtmlPresenter {
		return new DonorUpdateHtmlPresenter(
			new TwigTemplate(
				$this->getSkinTwig(), 'Donation_Confirmation.html.twig',
				$this->getDefaultTwigVariables()
			),
			$this->getUrlGenerator()
		);
	}

	public function newCancelDonationHtmlPresenter(): CancelDonationHtmlPresenter {
		return new CancelDonationHtmlPresenter(
			$this->getLayoutTemplate( 'Donation_Cancellation_Confirmation.html.twig' )
		);
	}

	public function newApplyForMembershipUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->getMembershipApplicationRepository(),
			$this->newMembershipApplicationTokenFetcher(),
			$this->newMembershipNotifier(),
			$this->newMembershipApplicationValidator(),
			$this->newApplyForMembershipPolicyValidator(),
			$this->newMembershipApplicationTracker(),
			$this->newMembershipApplicationPiwikTracker(),
			$this->getMembershipEventEmitter(),
			$this->getIncentiveFinder(),
			$this->newCreatePaymentUseCase()
		);
	}

	private function newMembershipNotifier(): MembershipNotifier {
		return new MembershipConfirmationMailer(
			$this->newApplyForMembershipMailer(),
			$this->newGetPaymentUseCase()
		);
	}

	private function newApplyForMembershipMailer(): MembershipTemplateMailerInterface {
		return $this->newErrorHandlingTemplateMailer(
			$this->getOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Membership_Application_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new MembershipConfirmationMailSubjectRenderer(
				$this->getMailTranslator(),
				'mail_subject_confirm_membership_application_active',
				'mail_subject_confirm_membership_application_sustaining'
			)
		);
	}

	private function newMembershipApplicationValidator(): MembershipApplicationValidator {
		return new MembershipApplicationValidator(
			$this->getEmailValidator()
		);
	}

	private function newMembershipApplicationTracker(): ApplicationTracker {
		return new DoctrineApplicationTracker( $this->getEntityManager() );
	}

	private function newMembershipApplicationPiwikTracker(): ApplicationPiwikTracker {
		return new DoctrineApplicationPiwikTracker( $this->getEntityManager() );
	}

	public function setPaymentDelayCalculator( PaymentDelayCalculator $paymentDelayCalculator ): void {
		$this->sharedObjects[PaymentDelayCalculator::class] = $paymentDelayCalculator;
	}

	private function newApplyForMembershipPolicyValidator(): ApplyForMembershipPolicyValidator {
		return new ApplyForMembershipPolicyValidator(
			$this->newTextPolicyValidator( 'fields' ),
			$this->config['email-address-blacklist']
		);
	}

	public function newCancelMembershipApplicationUseCase( string $updateToken ): CancelMembershipApplicationUseCase {
		return new CancelMembershipApplicationUseCase(
			$this->getMembershipApplicationAuthorizer( $updateToken ),
			$this->getMembershipApplicationRepository(),
			$this->newCancelMembershipApplicationMailer(),
			$this->newMembershipApplicationEventLogger(),
			$this->newCancelPaymentUseCase()
		);
	}

	private function getMembershipApplicationAuthorizer( string $updateToken = '', string $accessToken = '' ): ApplicationAuthorizer {
		return $this->createSharedObject(
			ApplicationAuthorizer::class,
			function () use ( $accessToken, $updateToken ): ApplicationAuthorizer {
				return new DoctrineApplicationAuthorizer( $this->getEntityManager(), $updateToken, $accessToken );
			}
		);
	}

	public function setMembershipApplicationRepository( ApplicationRepository $applicationRepository ): void {
		$this->sharedObjects[ApplicationRepository::class] = $applicationRepository;
	}

	public function getMembershipApplicationRepository(): ApplicationRepository {
		return $this->createSharedObject( ApplicationRepository::class, function () {
			return new LoggingApplicationRepository(
				new DoctrineApplicationRepository( $this->getEntityManager(), $this->newGetPaymentUseCase() ),
				$this->getLogger()
			);
		} );
	}

	public function setMembershipApplicationAuthorizer( ApplicationAuthorizer $authorizer ): void {
		$this->sharedObjects[ApplicationAuthorizer::class] = $authorizer;
	}

	private function newCancelMembershipApplicationMailer(): MembershipTemplateMailerInterface {
		return new TemplateBasedMailer(
			$this->getOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Membership_Application_Cancellation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_confirm_membership_application_cancellation' )
		);
	}

	public function newMembershipApplicationConfirmationUseCase( ShowApplicationConfirmationPresenter $presenter, string $accessToken ): ShowApplicationConfirmationUseCase {
		return new ShowApplicationConfirmationUseCase(
			$presenter,
			$this->getMembershipApplicationAuthorizer( '', $accessToken ),
			$this->getMembershipApplicationRepository(),
			$this->newMembershipApplicationTokenFetcher(),
			$this->newGetPaymentUseCase()
		);
	}

	public function newGetDonationUseCase( string $accessToken ): GetDonationUseCase {
		return new GetDonationUseCase(
			$this->newDonationAuthorizer( '', $accessToken ),
			$this->newDonationTokenFetcher(),
			$this->getDonationRepository()
		);
	}

	public function newDonationFormViolationPresenter(): DonationFormViolationPresenter {
		return new DonationFormViolationPresenter( $this->getDonationFormTemplate() );
	}

	public function newDonationFormPresenter(): DonationFormPresenter {
		return new DonationFormPresenter(
			$this->getDonationFormTemplate(),
			$this->newIsCustomDonationAmountValidator()
		);
	}

	private function getDonationFormTemplate(): TwigTemplate {
		return $this->getLayoutTemplate(
			'Donation_Form.html.twig',
			[
				'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForDonation(),
				'presetAmounts' => $this->getPresetAmountsSettings( 'donations' ),
				// TODO use Interval class (does not exist yet) when https://phabricator.wikimedia.org/T222636 is done
				'paymentIntervals' => [ 0, 1, 3, 6, 12 ],
				'userDataKey' => $this->getUserDataKeyGenerator()->getDailyKey(),
				'countries' => $this->getCountries(),
				'addressValidationPatterns' => $this->getValidationRules()->address,
			]
		);
	}

	private function getMembershipApplicationFormTemplate(): TwigTemplate {
		$validation = $this->getValidationRules();
		return $this->getLayoutTemplate( 'Membership_Application.html.twig', [
			'presetAmounts' => $this->getPresetAmountsSettings( 'membership' ),
			'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForMembershipApplication(),
			// TODO use Interval class (does not exist yet) when https://phabricator.wikimedia.org/T222636 is done
			'paymentIntervals' => [ 1, 3, 6, 12 ],
			'userDataKey' => $this->getUserDataKeyGenerator()->getDailyKey(),
			'countries' => $this->getCountries(),
			'addressValidationPatterns' => $validation->address,
			'dateOfBirthValidationPattern' => $validation->dateOfBirth,
			'incentives' => $this->getIncentives()
		] );
	}

	public function newMembershipApplicationFormPresenter(): MembershipApplicationFormPresenter {
		return new MembershipApplicationFormPresenter(
			$this->getMembershipApplicationFormTemplate(),
			$this->getIncentives()
		);
	}

	private function getIncentives(): array {
		// TODO hardcoded until the list gets extended in the near future
		return [ 'tote_bag' ];
	}

	public function getCountries(): array {
		$json = ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/countries.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();

		return ( json_decode( $json ) )->countries;
	}

	public function getValidationRules(): object {
		$json = ( new JsonStringReader(
			$this->getI18nDirectory() . '/data/validation.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();

		return json_decode( $json );
	}

	public function newHandleSofortPaymentNotificationUseCase( string $updateToken ): SofortPaymentNotificationUseCase {
		return new SofortPaymentNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationConfirmationMailer(),
			$this->newPaymentBookingService(),
			$this->newDonationEventLogger()
		);
	}

	public function newHandlePayPalPaymentCompletionNotificationUseCase( string $updateToken ): HandlePayPalPaymentCompletionNotificationUseCase {
		return new HandlePayPalPaymentCompletionNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationConfirmationMailer(),
			$this->newPaymentBookingService(),
			$this->newDonationEventLogger()
		);
	}

	public function getPayPalPaymentNotificationVerifier(): PaymentNotificationVerifier {
		return $this->createSharedObject( PaymentNotificationVerifier::class . '::Donation', function () {
			return new LoggingPaymentNotificationVerifier(
				new PayPalPaymentNotificationVerifier(
					new Client(),
					$this->config['paypal-donation']['base-url'],
					$this->config['paypal-donation']['account-address']
				),
				$this->getLogger()
			);
		} );
	}

	public function setPayPalPaymentNotificationVerifier( PaymentNotificationVerifier $verifier ): void {
		$this->sharedObjects[PaymentNotificationVerifier::class . '::Donation'] = $verifier;
	}

	public function getPayPalMembershipFeeNotificationVerifier(): PaymentNotificationVerifier {
		return $this->createSharedObject( PaymentNotificationVerifier::class . '::Membership', function () {
			return new LoggingPaymentNotificationVerifier(
				new PayPalPaymentNotificationVerifier(
					new Client(),
					$this->config['paypal-membership']['base-url'],
					$this->config['paypal-membership']['account-address']
				),
				$this->getLogger()
			);
		} );
	}

	public function setPayPalMembershipFeeNotificationVerifier( PaymentNotificationVerifier $verifier ): void {
		$this->sharedObjects[ PaymentNotificationVerifier::class . '::Membership' ] = $verifier;
	}

	public function newCreditCardNotificationUseCase( string $updateToken ): CreditCardNotificationUseCase {
		return new CreditCardNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationConfirmationMailer(),
			$this->newPaymentBookingService(),
			$this->newDonationEventLogger()
		);
	}

	public function newCancelMembershipApplicationHtmlPresenter(): CancelMembershipApplicationHtmlPresenter {
		return new CancelMembershipApplicationHtmlPresenter(
			$this->getLayoutTemplate( 'Membership_Application_Cancellation_Confirmation.html.twig' )
		);
	}

	public function newMembershipApplicationConfirmationHtmlPresenter(): MembershipApplicationConfirmationHtmlPresenter {
		return new MembershipApplicationConfirmationHtmlPresenter(
			$this->getLayoutTemplate( 'Membership_Application_Confirmation.html.twig' ),
			$this->getUrlGenerator()
		);
	}

	public function newMembershipFormViolationPresenter(): MembershipFormViolationPresenter {
		return new MembershipFormViolationPresenter(
			$this->getMembershipApplicationFormTemplate()
		);
	}

	public function newCreditCardNotificationPresenter(): CreditCardNotificationPresenter {
		return new CreditCardNotificationPresenter(
			$this->config['creditcard']['return-url']
		);
	}

	public function setDonationTokenGenerator( TokenGenerator $tokenGenerator ): void {
		$this->sharedObjects[TokenGenerator::class] = $tokenGenerator;
		$this->getDonationContextFactory()->setTokenGenerator( $tokenGenerator );
	}

	public function setMembershipTokenGenerator( MembershipTokenGenerator $tokenGenerator ): void {
		$this->sharedObjects[MembershipTokenGenerator::class] = $tokenGenerator;
		$this->getMembershipContextFactory()->setTokenGenerator( $tokenGenerator );
	}

	public function disableDoctrineSubscribers(): void {
		$this->addDoctrineSubscribers = false;
	}

	private function newDonationTokenFetcher(): DonationTokenFetcher {
		return new DoctrineDonationTokenFetcher(
			$this->getEntityManager()
		);
	}

	private function newMembershipApplicationTokenFetcher(): ApplicationTokenFetcher {
		return new DoctrineApplicationTokenFetcher(
			$this->getEntityManager()
		);
	}

	private function newDonationModerationService(): ModerationService {
		return new ModerationService(
			$this->newDonationAmountPolicyValidator(),
			$this->newTextPolicyValidator( 'fields' ),
			$this->config['email-address-blacklist']
		);
	}

	private function newDonationAmountPolicyValidator(): AmountPolicyValidator {
		// in the future, this might come from the configuration
		return new AmountPolicyValidator( 1000, 1000 );
	}

	public function newSystemMessageResponse( string $message ): string {
		return $this->getLayoutTemplate( 'System_Message.html.twig' )
			->render( [ 'message' => $message ] );
	}

	private function newAddCommentValidator(): AddCommentValidator {
		return new AddCommentValidator();
	}

	public function setCampaignCache( CacheInterface $cache ): void {
		$this->sharedObjects['Cache::Campaign'] = $cache;
	}

	private function getCampaignCache(): CacheInterface {
		return $this->createSharedObject( 'Cache::Campaign', static function () {
			return new Psr16Cache( new ArrayAdapter() );
		} );
	}

	public function setLogger( LoggerInterface $logger ): void {
		$this->sharedObjects[LoggerInterface::class . '::Application'] = $logger;
	}

	public function setPaypalLogger( LoggerInterface $logger ): void {
		$this->sharedObjects[LoggerInterface::class . '::Paypal'] = $logger;
	}

	public function setSofortLogger( LoggerInterface $logger ): void {
		$this->sharedObjects[LoggerInterface::class . '::Sofort'] = $logger;
	}

	private function newIbanValidator(): KontoCheckIbanValidator {
		return new KontoCheckIbanValidator();
	}

	private function newIbanBlockList(): IbanBlockList {
		return new IbanBlockList( $this->config['banned-ibans'] );
	}

	public function newDonationAcceptedEventHandler( string $updateToken ): DonationAcceptedEventHandler {
		return new DonationAcceptedEventHandler(
			$this->newDonationAuthorizer( $updateToken ),
			$this->getDonationRepository(),
			$this->newDonationConfirmationMailer()
		);
	}

	public function newPageNotFoundHtmlPresenter(): PageNotFoundPresenter {
		return new PageNotFoundPresenter( $this->getLayoutTemplate( 'Page_Not_Found.html.twig' ) );
	}

	public function getI18nDirectory(): string {
		return $this->getAbsolutePath( $this->config['i18n-base-path'] ) . '/' . $this->getLocale();
	}

	/**
	 * If the pathname does not start with a slash, make the path absolute to root dir of application
	 *
	 * @param string $path
	 * @return string
	 */
	private function getAbsolutePath( string $path ): string {
		if ( $path[0] === '/' ) {
			return $path;
		}
		return __DIR__ . '/../../' . $path;
	}

	public function setContentPagePageSelector( PageSelector $pageSelector ): void {
		$this->sharedObjects[PageSelector::class] = $pageSelector;
	}

	public function getContentPagePageSelector(): PageSelector {
		return $this->createSharedObject( PageSelector::class, function () {
			$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/pages.json' );
			$config = json_decode( $json, true ) ?? [];

			return new PageSelector( $config );
		} );
	}

	public function setContentProvider( ContentProvider $contentProvider ): void {
		$this->sharedObjects[ContentProvider::class] = $contentProvider;
	}

	private function getContentProvider(): ContentProvider {
		return $this->createSharedObject( ContentProvider::class, function () {
			return new ContentProvider( [
				'content_path' => $this->getI18nDirectory(),
				'cache' => $this->config['twig']['enable-cache'] ? $this->getCachePath() . '/content' : false,
				'globals' => [
					'basepath' => $this->config['web-basepath']
				]
			] );
		} );
	}

	public function newMailTemplateFilenameTraversable(): MailTemplateFilenameTraversable {
		return new MailTemplateFilenameTraversable(
			$this->config['mailer-twig']['loaders']['filesystem']['template-dir']
		);
	}

	public function getUrlGenerator(): UrlGenerator {
		if ( !isset( $this->sharedObjects[UrlGenerator::class] ) ) {
			throw new \LogicException( sprintf(
				'UrlGenerator is a setter dependency that must be set before calling "%s"!',
				__METHOD__
			) );
		}
		return $this->sharedObjects[UrlGenerator::class];
	}

	public function setUrlGenerator( UrlGenerator $urlGenerator ): void {
		$this->sharedObjects[UrlGenerator::class] = $urlGenerator;
	}

	public function getDonationSubmissionRateLimiter(): SubmissionRateLimit {
		return new SubmissionRateLimit(
			self::DONATION_RATE_LIMIT_SESSION_KEY,
			new \DateInterval( $this->config['donation-timeframe-limit'] )
		);
	}

	public function getMembershipSubmissionRateLimiter(): SubmissionRateLimit {
		return new SubmissionRateLimit(
			self::MEMBERSHIP_RATE_LIMIT_SESSION_KEY,
			new \DateInterval( $this->config['membership-application-timeframe-limit'] )
		);
	}

	public function getPaymentTypesSettings(): PaymentTypesSettings {
		return $this->createSharedObject( PaymentTypesSettings::class, function (): PaymentTypesSettings {
			return new PaymentTypesSettings( $this->config['payment-types'] );
		} );
	}

	/**
	 * @param string $presetType
	 * @return Euro[]
	 */
	public function getPresetAmountsSettings( string $presetType ): array {
		return array_map( static function ( int $amount ) {
			return Euro::newFromCents( $amount );
		}, $this->config['preset-amounts'][$presetType] );
	}

	public function newIsCustomDonationAmountValidator(): IsCustomAmountValidator {
		return new IsCustomAmountValidator( $this->getPresetAmountsSettings( 'donations' ) );
	}

	public function getSkinDirectory(): string {
		return $this->createSharedObject( 'SKIN_DIRECTORY', function (): string {
			return $this->getAbsolutePath( 'skins/' . $this->config['skin'] . '/templates' );
		} );
	}

	public function setSkinDirectory( string $skinDirectory ): void {
		$this->sharedObjects['SKIN_DIRECTORY'] = $skinDirectory;
	}

	/**
	 * @return Campaign[]
	 */
	private function getCampaigns(): array {
		$builder = new CampaignBuilder( new \DateTimeZone( $this->config['campaigns']['timezone'] ) );
		$configFiles = array_map(
			function ( string $campaignConfigFile ) {
				return $this->getAbsolutePath( 'app/config/' . $campaignConfigFile );
			},
			$this->config['campaigns']['configurations']
		);
		$loader = $this->getCampaignConfigurationLoader();
		return $builder->getCampaigns( $loader->loadCampaignConfiguration( ...$configFiles ) );
	}

	public function getCampaignConfigurationLoader(): CampaignConfigurationLoaderInterface {
		return $this->createSharedObject( CampaignConfigurationLoaderInterface::class, function (): CampaignConfigurationLoader {
			return new CampaignConfigurationLoader( new SimpleFileFetcher(), $this->getCampaignCache() );
		} );
	}

	public function setCampaignConfigurationLoader( CampaignConfigurationLoaderInterface $loader ): void {
		$this->sharedObjects[CampaignConfigurationLoaderInterface::class] = $loader;
	}

	private function newCampaignFeatures(): Set {
		// TODO Cache features so we don't have to parse the campaign config on every request
		$factory = new CampaignFeatureBuilder( ...$this->getCampaigns() );
		return $factory->getFeatures();
	}

	private function getFeatureToggle(): FeatureToggle {
		return $this->createSharedObject( FeatureToggle::class, function (): FeatureToggle {
			$doorkeeper = new Doorkeeper( $this->newCampaignFeatures() );
			$requestor = new Requestor();
			foreach ( $this->getSelectedBuckets() as $bucket ) {
				$requestor = $requestor->withStringHash( $bucket->getId() );
			}
			$doorkeeper->setRequestor( $requestor );
			return new DoorkeeperFeatureToggle( $doorkeeper );
		} );
	}

	private function getChoiceFactory(): ChoiceFactory {
		return $this->createSharedObject( ChoiceFactory::class, function (): ChoiceFactory {
			return new ChoiceFactory( $this->getFeatureToggle() );
		} );
	}

	public function getAddressType(): ?string {
		return $this->getChoiceFactory()->getAddressType();
	}

	/**
	 * TODO: Remove this after C21_WMDE_Test_12
	 *
	 * @return bool
	 */
	public function forceChoiceCookieStorage(): bool {
		return $this->getChoiceFactory()->forceChoiceCookieStorage();
	}

	public function getBucketSelector(): BucketSelector {
		return $this->createSharedObject( BucketSelector::class, function (): BucketSelector {
			return new BucketSelector( $this->getCampaignCollection(), new RandomBucketSelection() );
		} );
	}

	private function getBucketLogger(): BucketLogger {
		return $this->createSharedObject( 'bucketLogger', function () {
			return new BestEffortBucketLogger(
				new DatabaseBucketLogger(
					new DoctrineBucketLogRepository( $this->getEntityManager() )
				),
				$this->getLogger()
			);
		} );
	}

	public function setBucketLogger( BucketLogger $logger ): void {
		$this->sharedObjects['bucketLogger'] = $logger;
	}

	public function getSelectedBuckets(): array {
		// when in the web environment, selected buckets will be set by BucketSelectionServiceProvider during request processing
		// other environments (testing/cli) may set this during setup
		if ( !isset( $this->sharedObjects['selectedBuckets'] ) ) {
			throw new \LogicException( 'Buckets were not selected yet, you must not initialize A/B tested classes before the app processes the request.' );
		}
		return $this->sharedObjects['selectedBuckets'];
	}

	public function setSelectedBuckets( array $selectedBuckets ): void {
		$this->sharedObjects['selectedBuckets'] = $selectedBuckets;
	}

	public function getCampaignCollection(): CampaignCollection {
		return new CampaignCollection( ...$this->getCampaigns() );
	}

	public function getUseOfFundsRenderer(): \Closure {
		if ( $this->config['skin'] === 'laika' ) {
			$this->getTranslationCollector()->addTranslationFile( $this->getI18nDirectory() . '/messages/useOfFundsMessages.json' );
			$template = $this->getLayoutTemplate( 'Funds_Usage.html.twig', [
				'use_of_funds_content' => $this->getApplicationOfFundsContent(),
				'use_of_funds_messages' => $this->getApplicationOfFundsMessages()
			] );
			return static function () use ( $template )  {
				return $template->render( [] );
			};
		} elseif ( $this->config['skin'] === 'test' ) {
			// we don't care what happens in test
			return static function () {
				return 'Test rendering: Use of funds';
			};
		}

		throw new \InvalidArgumentException( 'Unsupported skin for use of funds:' . $this->config['skin'] );
	}

	public function getTranslationCollector(): TranslationsCollector {
		return $this->createSharedObject( TranslationsCollector::class, function (): TranslationsCollector {
			$translationsCollector = new TranslationsCollector( new SimpleFileFetcher() );
			$translationsCollector->addTranslationFile( $this->getI18nDirectory() . '/messages/messages.json' );
			$translationsCollector->addTranslationFile( $this->getI18nDirectory() . '/messages/membershipTypes.json' );
			$translationsCollector->addTranslationFile( $this->getI18nDirectory() . '/messages/validations.json' );
			return $translationsCollector;
		} );
	}

	public function setDoctrineConfiguration( ?Configuration $doctrineConfiguration ): FunFunFactory {
		$this->sharedObjects[Configuration::class] = $doctrineConfiguration;
		return $this;
	}

	private function getDoctrineConfiguration(): Configuration {
		if ( !isset( $this->sharedObjects[Configuration::class] ) ) {
			throw new \LogicException( 'Environment-specific Doctrine configuration was not initialized!' );
		}
		return $this->sharedObjects[Configuration::class];
	}

	private function getDonationContextFactory(): DonationContextFactory {
		return $this->createSharedObject( DonationContextFactory::class, function (): DonationContextFactory {
			return new DonationContextFactory(
				[
					'token-length' => $this->config['token-length'],
					'token-validity-timestamp' => $this->config['token-validity-timestamp']
				],
			);
		} );
	}

	private function getMembershipContextFactory(): MembershipContextFactory {
		return $this->createSharedObject( MembershipContextFactory::class, function (): MembershipContextFactory {
			return new MembershipContextFactory(
				[
					'token-length' => $this->config['token-length'],
					'token-validity-timestamp' => $this->config['token-validity-timestamp']
				],
			);
		} );
	}

	private function getPaymentContextFactory(): PaymentContextFactory {
		return $this->createSharedObject(
			PaymentContextFactory::class,
			fn(): PaymentContextFactory => new PaymentContextFactory()
		);
	}

	private function getUserDataKeyGenerator(): UserDataKeyGenerator {
		return $this->createSharedObject( UserDataKeyGenerator::class, function (): UserDataKeyGenerator {
			return new UserDataKeyGenerator( $this->config['user-data-key'], new SystemClock() );
		} );
	}

	private function getEventDispatcher(): EventDispatcher {
		return $this->createSharedObject( EventDispatcher::class, function (): EventDispatcher {
			$dispatcher = new EventDispatcher();
			$this->setupEventListeners( $dispatcher );
			return $dispatcher;
		} );
	}

	private function getDonationEventEmitter(): DonationEventEmitter {
		return $this->createSharedObject( DonationEventEmitter::class, function (): DonationEventEmitter {
			return new DonationEventEmitter( $this->getEventDispatcher() );
		} );
	}

	private function getMembershipEventEmitter(): MembershipEventEmitter {
		return $this->createSharedObject( MembershipEventEmitter::class, function (): MembershipEventEmitter {
			return new MembershipEventEmitter( $this->getEventDispatcher() );
		} );
	}

	private function setupEventListeners( EventDispatcher $dispatcher ): void {
		// TODO: Move this initialisation into an initialiser class
		new CreateAddressChangeHandler( $this->getEntityManager(), $dispatcher );

		$bucketLoggingHandler = $this->getBucketLoggingHandler();
		foreach ( BucketLoggingHandler::getSubscribedEvents() as $event => $method ) {
			$dispatcher->addEventListener( $event, [ $bucketLoggingHandler, $method ] );
		}
	}

	public function getBucketLoggingHandler(): BucketLoggingHandler {
		return $this->createSharedObject( BucketLoggingHandler::class, function (): BucketLoggingHandler {
			return new BucketLoggingHandler(
				$this->getBucketLogger(),
				/** @return Bucket[] */
				function (): array {
					// Defer execution with anonymous function because buckets might not be selected at call time
					return $this->getSelectedBuckets();
				}
			);
		} );
	}

	private function getPaymentProviderItemsTranslator(): TranslatorInterface {
		return $this->createSharedObject( TranslatorInterface::class . '::PaymentProviderItemTranslator', function (): TranslatorInterface {
			$translator = new JsonTranslator( new SimpleFileFetcher() );
			return $translator
				->addFile( $this->getI18nDirectory() . '/messages/paymentIntervals.json' )
				->addFile( $this->getI18nDirectory() . '/messages/paymentProvider.json' );
		} );
	}

	public function setPaymentProviderItemsTranslator( TranslatorInterface $translator ): void {
		$this->sharedObjects[TranslatorInterface::class . '::PaymentProviderItemTranslator'] = $translator;
	}

	private function getMailTranslator(): TranslatorInterface {
		return $this->createSharedObject( TranslatorInterface::class . '::MailTranslator', function (): TranslatorInterface {
			$translator = new JsonTranslator( new SimpleFileFetcher() );
			return $translator
				->addFile( $this->getI18nDirectory() . '/messages/mail.json' )
				->addFile( $this->getI18nDirectory() . '/messages/paymentTypes.json' )
				->addFile( $this->getI18nDirectory() . '/messages/membershipTypes.json' );
		} );
	}

	public function setMailTranslator( TranslatorInterface $translator ): void {
		$this->sharedObjects[TranslatorInterface::class . '::MailTranslator'] = $translator;
	}

	private function getSiteMetaData(): array {
		$fileFetcher = new SimpleFileFetcher();
		$metadata = json_decode( $fileFetcher->fetchFile( $this->getI18nDirectory() . '/messages/siteMetadata.json' ), true );
		$metadata['page_titles'] = json_decode( $fileFetcher->fetchFile( $this->getI18nDirectory() . '/messages/pageTitles.json' ), true );
		return $metadata;
	}

	private function getDayOfWeekName(): string {
		$fileFetcher = new SimpleFileFetcher();
		$daysOfWeek = json_decode( $fileFetcher->fetchFile( $this->getI18nDirectory() . '/messages/daysOfTheWeek.json' ), true );
		return $daysOfWeek[date( 'N' )];
	}

	public function getCreditCardLogger(): LoggerInterface {
		return $this->createSharedObject( LoggerInterface::class . '::CreditCard', static function (): LoggerInterface {
			return new NullLogger();
		} );
	}

	public function setCreditCardLogger( LoggerInterface $logger ): void {
		$this->sharedObjects[LoggerInterface::class . '::CreditCard'] = $logger;
	}

	public function getValidationErrorLogger(): ValidationErrorLogger {
		return $this->createSharedObject( ValidationErrorLogger::class, function (): ValidationErrorLogger {
			return new ValidationErrorLogger( $this->getLogger() );
		} );
	}

	private function getMailTemplateDirectory() {
		return $this->createSharedObject( 'MAIL_TEMPLATE_DIRECTORY', function (): string {
			return $this->getAbsolutePath( $this->config['mailer-twig']['loaders']['filesystem']['template-dir'] );
		} );
	}

	public function setMailTemplateDirectory( string $directory ): void {
		$this->sharedObjects['MAIL_TEMPLATE_DIRECTORY'] = $directory;
	}

	private function getApplicationEnvironment(): string {
		return $_SERVER['APP_ENV'] ?? 'dev';
	}

	private function getIncentiveFinder(): IncentiveFinder {
		return new DoctrineIncentiveFinder( $this->getEntityManager() );
	}

	private function newMembershipApplicationEventLogger(): MembershipApplicationEventLogger {
		return new DoctrineMembershipApplicationEventLogger( $this->getEntityManager() );
	}

	public function newFindCitiesUseCase(): FindCitiesUseCase {
		return new FindCitiesUseCase(
			new DoctrineLocationRepository( $this->getEntityManager() )
		);
	}

	private function getLocale(): string {
		if ( !isset( $this->sharedObjects[ 'locale' ] ) ) {
			throw new \LogicException( 'Locale was not selected yet, you must not initialize locale dependant classes before the app processes the request.' );
		}
		return $this->sharedObjects[ 'locale' ];
	}

	public function setLocale( string $locale ): void {
		$this->sharedObjects[ 'locale' ] = $locale;
	}

	private function getRootPath(): string {
		return $this->getAbsolutePath( __DIR__ . '/../..' );
	}

	private function newCancelPaymentUseCase(): CancelPaymentUseCase {
		return new CancelPaymentUseCase( $this->newPaymentRepository() );
	}

	private function newPaymentBookingService(): PaymentBookingService {
		return new PaymentBookingServiceWithUseCase(
			new BookPaymentUseCase(
				$this->newPaymentRepository(),
				$this->newPaymentIDRepository(),
				new ExternalVerificationServiceFactory(
					new Client(),
					$this->config['paypal-donation']['base-url'],
					$this->config['paypal-donation']['paypal-donation']
				),
				$this->newDoctrineTransactionIdFinder()
			)
		);
	}

	private function newDoctrineTransactionIdFinder(): DoctrineTransactionIdFinder {
		return new DoctrineTransactionIdFinder( $this->getConnection() );
	}
}
