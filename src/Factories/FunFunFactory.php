<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use FileFetcher\ErrorLoggingFileFetcher;
use FileFetcher\SimpleFileFetcher;
use GuzzleHttp\Client;
use Locale;
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
use WMDE\Fundraising\ContentProvider\TwigContentProviderConfig;
use WMDE\Fundraising\ContentProvider\TwigContentProviderFactory;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineCommentFinder;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationEventLogger;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationExistsChecker;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationIdRepository;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\DonationContext\DataAccess\ModerationReasonRepository as DonationModerationReasonRepository;
use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationExistsChecker;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationIdRepository;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\DonationContext\DonationAcceptedEventHandler;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizationChecker;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationMailer;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingCommentFinder;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingDonationRepository;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\DonationContext\Services\PaymentBookingService;
use WMDE\Fundraising\DonationContext\Services\PaymentBookingServiceWithUseCase;
use WMDE\Fundraising\DonationContext\Services\PaypalBookingService;
use WMDE\Fundraising\DonationContext\Services\PaypalBookingServiceWithUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\CreatePaymentWithUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\DonationPaymentValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\Moderation\ModerationService as DonationModerationService;
use WMDE\Fundraising\DonationContext\UseCases\BookDonationUseCase\BookDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\HandlePaypalPaymentWithoutDonation\HandlePaypalPaymentWithoutDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorValidator;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationContextFactory;
use WMDE\Fundraising\Frontend\Authentication\DonationUrlAuthenticationLoader;
use WMDE\Fundraising\Frontend\Authentication\MembershipUrlAuthenticationLoader;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationLoader;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthorizationChecker;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\DoctrineTokenRepository;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\FallbackTokenRepository;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\LenientAuthorizationChecker;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\PersistentAuthorizer;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\TokenRepository;
use WMDE\Fundraising\Frontend\Authentication\RandomTokenGenerator;
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
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\DoorkeeperFeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BestEffortBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\DatabaseBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\RandomBucketSelection;
use WMDE\Fundraising\Frontend\FeatureToggle\FeatureReader;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\CreateAddressChangeHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DonationEventEmitter;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\MembershipEventEmitter;
use WMDE\Fundraising\Frontend\Infrastructure\FileFeatureReader;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\AdminModerationMailRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingTemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\GetInTouchMailerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailSubjectRendererInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailTemplateFilenameTraversable;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\NullMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\OperatorMailer;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting\MembershipImpressionCounter;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting\NullMembershipImpressionCounter;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalAdapterConfigLoader;
use WMDE\Fundraising\Frontend\Infrastructure\SubmissionRateLimit;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\JsonTranslator;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Infrastructure\TranslationsCollector;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\UserDataKeyGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;
use WMDE\Fundraising\Frontend\Presentation\ActiveFeatureRenderer;
use WMDE\Fundraising\Frontend\Presentation\BucketPropertyExtractor;
use WMDE\Fundraising\Frontend\Presentation\CampaignPropertyExtractor;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
use WMDE\Fundraising\Frontend\Presentation\Honorifics;
use WMDE\Fundraising\Frontend\Presentation\PaymentTypesSettings;
use WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListJsonPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CommentListRssPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\CreditCardNotificationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;
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
use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizationChecker;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizer;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineIncentiveFinder;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineMembershipIdGenerator;
use WMDE\Fundraising\MembershipContext\DataAccess\IncentiveFinder;
use WMDE\Fundraising\MembershipContext\DataAccess\ModerationReasonRepository as MembershipModerationReasonRepository;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\MembershipContext\Infrastructure\PaymentServiceFactory;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipTemplateMailerInterface;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationTracker;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\Moderation\ModerationService as MembershipModerationService;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\Notification\MailMembershipApplicationNotifier;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationUseCase;
use WMDE\Fundraising\PaymentContext\DataAccess\DoctrinePaymentIdRepository;
use WMDE\Fundraising\PaymentContext\DataAccess\DoctrinePaymentRepository;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\IbanBlockList;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentIdRepository;
use WMDE\Fundraising\PaymentContext\Domain\PaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentRepository;
use WMDE\Fundraising\PaymentContext\Domain\PaymentValidator;
use WMDE\Fundraising\PaymentContext\Domain\PayPalPaymentIdentifierRepository;
use WMDE\Fundraising\PaymentContext\PaymentContextFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\ExternalVerificationServiceFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\PayPal\PayPalVerificationService;
use WMDE\Fundraising\PaymentContext\Services\KontoCheck\KontoCheckBankDataGenerator;
use WMDE\Fundraising\PaymentContext\Services\KontoCheck\KontoCheckIbanValidator;
use WMDE\Fundraising\PaymentContext\Services\PaymentProviderAdapterFactoryImplementation;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\CharacterPickerPaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\RandomCharacterIndexGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentReferenceCodeGenerator\UniquePaymentReferenceCodeGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentURLFactory;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\CreditCardURLGeneratorConfig;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\LegacyPayPalURLGeneratorConfig;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\Sofort\SofortClient;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\SofortURLGeneratorConfig;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PaypalAPI;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalPaymentProviderAdapterConfig;
use WMDE\Fundraising\PaymentContext\Services\SofortLibClient;
use WMDE\Fundraising\PaymentContext\Services\TransactionIdFinder\DoctrineTransactionIdFinder;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\BookPaymentUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationService;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationServiceFactory;
use WMDE\Fundraising\PaymentContext\UseCases\CreateBookedPayPalPayment\CreateBookedPayPalPaymentUseCase;
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
use WMDE\FunValidators\Validators\AmountPolicyValidator;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\TextPolicyValidator;

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
			$configuration = new Configuration();
			$configuration->setSchemaManagerFactory( new DefaultSchemaManagerFactory() );
			return DriverManager::getConnection( $this->config['db'], $configuration );
		} );
	}

	public function getEntityManager(): EntityManager {
		return $this->createSharedObject( EntityManager::class, function () {
			return $this->getDoctrineFactory()->getEntityManager();
		} );
	}

	private function getDoctrineFactory(): DoctrineFactory {
		return $this->createSharedObject( DoctrineFactory::class, function () {
			return new DoctrineFactory(
				$this->getConnection(),
				$this->getDoctrineConfiguration(),
				$this->getBoundedContextFactoryCollection()
			);
		} );
	}

	public function getBoundedContextFactoryCollection(): ContextFactoryCollection {
		return $this->createSharedObject( ContextFactoryCollection::class, static function () {
			return new ContextFactoryCollection(
				new DonationContextFactory(),
				new MembershipContextFactory(),
				new PaymentContextFactory(),
				new SubscriptionContextFactory(),
				new AddressChangeContextFactory(),
				new BucketTestingContextFactory(),
				new AutocompleteContextFactory(),
				new AuthenticationContextFactory(),
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
				new EmailValidator( new NullDomainNameValidator() ),
				$this->newSubscriptionDuplicateValidator(),
			);
		} );
	}

	public function getModerationReasonRepositoryForMembership(): MembershipModerationReasonRepository {
		return $this->createSharedObject( MembershipModerationReasonRepository::class, function (): MembershipModerationReasonRepository {
			return new MembershipModerationReasonRepository( $this->getEntityManager() );
		} );
	}

	public function getModerationReasonRepositoryForDonation(): DonationModerationReasonRepository {
		return $this->createSharedObject( DonationModerationReasonRepository::class, function (): DonationModerationReasonRepository {
			return new DonationModerationReasonRepository( $this->getEntityManager() );
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

	public function newConfirmSubscriptionHtmlPresenter(): ConfirmSubscriptionHtmlPresenter {
		return new ConfirmSubscriptionHtmlPresenter(
			$this->getLayoutTemplate( 'Subscription_Confirmation.twig' )
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
		$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/contact_categories.json' );
		return json_decode( $json, true );
	}

	public function getApplicationOfFundsContent(): string {
		return ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/use_of_funds_content.json' );
	}

	public function getApplicationOfFundsMessages(): string {
		return ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/messages/useOfFundsMessages.json' );
	}

	public function getFaqContent(): string {
		return ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/faq.json' );
	}

	public function getSupportersList(): string {
		return ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/supporters.json' );
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
				$this->getGreetingGenerator(),
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
			'selectedBuckets' => BucketPropertyExtractor::listBucketIds( ...$this->getSelectedBuckets() ),
			'allowedCampaignParameters' => CampaignPropertyExtractor::listURLKeys( ...$this->getCampaigns() ),
			'activeFeatures' => ActiveFeatureRenderer::renderActiveFeatureIds( $this->getFeatureReader()->getFeatures() )
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
			$this->getSubscriptionMessenger(),
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
			$this->getSubscriptionMessenger(),
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

	public function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->getContactValidator(),
			$this->newContactOperatorMailer(),
			$this->newContactUserMailer()
		);
	}

	private function newContactUserMailer(): GetInTouchMailerInterface {
		return $this->newTemplateMailer(
			$this->getContactMessenger(),
			new TwigTemplate( $this->getMailerTwig(), 'Contact_Confirm_to_User.txt.twig' ),
			new BasicMailSubjectRenderer( $this->getMailTranslator(), 'mail_subject_getintouch' )
		);
	}

	private function newContactOperatorMailer(): OperatorMailer {
		return new OperatorMailer(
			$this->getContactMessenger(),
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

	public function setFeatureReader( FeatureReader $featureReader ): void {
		$this->sharedObjects[FeatureReader::class] = $featureReader;
	}

	public function getFeatureReader(): FeatureReader {
		return $this->createSharedObject( FeatureReader::class, function (): FeatureReader {
			return new FileFeatureReader(
				new SimpleFileFetcher(),
				$this->getContentPath() . '/data/features.json',
				$this->getLogger()
			);
		} );
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

	private function getDonationMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'donation', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				new EmailAddress( $this->config['contact-info']['donation']['email'] ),
				$this->config['contact-info']['donation']['name']
			);
		} );
	}

	private function getMembershipMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'membership', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				new EmailAddress( $this->config['contact-info']['membership']['email'] ),
				$this->config['contact-info']['membership']['name']
			);
		} );
	}

	private function getSubscriptionMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'subscription', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				new EmailAddress( $this->config['contact-info']['subscription']['email'] ),
				$this->config['contact-info']['subscription']['name']
			);
		} );
	}

	private function getContactMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'contact', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				new EmailAddress( $this->config['contact-info']['contact']['email'] ),
				$this->config['contact-info']['contact']['name']
			);
		} );
	}

	public function setContactMessenger( Messenger $messenger ): void {
		$this->sharedObjects[ Messenger::class . 'contact' ] = $messenger;
	}

	private function getAdminMessenger(): Messenger {
		return $this->createSharedObject( Messenger::class . 'admin', function (): Messenger {
			return new Messenger(
				$this->getMailer(),
				new EmailAddress( $this->config['contact-info']['admin']['email'] ),
				$this->config['contact-info']['admin']['name']
			);
		} );
	}

	private function getMailer(): MailerInterface {
		return $this->createSharedObject( MailerInterface::class, static function (): MailerInterface {
			return new Mailer( new NullTransport() );
		} );
	}

	/**
	 * Mailer is set by Symfony dependency injection defined in services.yaml
	 *
	 * @noinspection PhpUnused
	 *
	 * @param MailerInterface $mailer
	 * @return void
	 */
	public function setMailer( MailerInterface $mailer ): void {
		$this->sharedObjects[ MailerInterface::class ] = $mailer;
	}

	public function setNullMessengers(): void {
		$messenger = new Messenger(
			new Mailer( new NullTransport() ),
			new EmailAddress( $this->config['contact-info']['donation']['email'] )
		);

		$this->sharedObjects[ Messenger::class . 'donation' ] = $messenger;
		$this->sharedObjects[ Messenger::class . 'membership' ] = $messenger;
		$this->sharedObjects[ Messenger::class . 'subscription' ] = $messenger;
		$this->sharedObjects[ Messenger::class . 'contact' ] = $messenger;
		$this->sharedObjects[ Messenger::class . 'admin' ] = $messenger;
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
		return new TextPolicyValidator(
			new WordListFileReader(
				$fetcher,
				$this->getTextPolicyPathWithFallback( $policyName, 'denied_words', 'badwords' )
			),
			new WordListFileReader(
				$fetcher,
				$this->getTextPolicyPathWithFallback( $policyName, 'allowed_words', 'whitewords' )
			)
		);
	}

	private function getTextPolicyPathWithFallback( string $policyName, string $fileNameKey, string $fallbackNameKey = '' ): string {
		$fileName = $this->config['text-policies'][$policyName][$fileNameKey] ?? '';
		// Remove this fallback (and the fallback parameter) when we have updated all configuration files
		// See https://phabricator.wikimedia.org/T352788
		if ( $fileName === '' && $fallbackNameKey !== '' ) {
			$fileName = $this->config['text-policies'][$policyName][$fallbackNameKey] ?? '';
		}
		return $fileName ? $this->getAbsolutePath( $fileName ) : '';
	}

	private function newCommentPolicyValidator(): TextPolicyValidator {
		return $this->newTextPolicyValidator( 'comment' );
	}

	public function newAddDonationUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->getDonationIdRepository(),
			$this->getDonationRepository(),
			$this->newDonationValidator(),
			$this->newDonationModerationService(),
			$this->newDonationMailer(),
			$this->getDonationAuthorizer(),
			$this->getDonationEventEmitter(),
			new CreatePaymentWithUseCase(
				$this->newCreatePaymentUseCaseForDonations(),
				$this->getPaymentTypesSettings()->getPaymentTypesForDonation()
			)
		);
	}

	public function newCreatePaymentUseCaseForDonations(): CreatePaymentUseCase {
		return new CreatePaymentUseCase(
			$this->getPaymentIdRepository(),
			$this->getPaymentRepository(),
			$this->newPaymentReferenceCodeGenerator(),
			$this->newPaymentValidator(),
			$this->newCheckIbanUseCase(),
			new PaymentURLFactory(
				$this->newCreditCardUrlGeneratorConfig(),
				$this->getLegacyPayPalUrlConfigForDonations(),
				$this->getSofortUrlGeneratorConfigForDonations(),
				$this->getSofortClient(),
				$this->getUrlGenerator()->generateAbsoluteUrl( Routes::SHOW_DONATION_CONFIRMATION ),
				useLegacyPayPalUrlGenerator: $this->useLegacyPaypalAPI()
			),
			new PaymentProviderAdapterFactoryImplementation(
				$this->getPayPalApiClient(),
				$this->getPayPalAdapterConfigForDonations(),
				$this->getPaymentRepository(),
				useLegacyPaypalPaymentAdapter: $this->useLegacyPaypalAPI()
			)
		);
	}

	/**
	 * Temporary feature flag to switch between "Legacy" PayPal API (where we pass all payment data via URL)
	 * and "Modern" PayPal API (where we contact PayPal on their API to "announce" payments and get a redirect URL back)
	 * See https://phabricator.wikimedia.org/T329159 for the ticket for fully migrating to the modern API
	 */
	public function useLegacyPaypalAPI(): bool {
		return true;
	}

	public function newCreatePaymentUseCaseForMemberships(): CreatePaymentUseCase {
		return new CreatePaymentUseCase(
			$this->getPaymentIdRepository(),
			$this->getPaymentRepository(),
			$this->newPaymentReferenceCodeGenerator(),
			$this->newPaymentValidator(),
			$this->newCheckIbanUseCase(),
			new PaymentURLFactory(
				$this->newCreditCardUrlGeneratorConfig(),
				// We can pass the donation config in here, because it will never be used
				$this->getLegacyPayPalUrlConfigForDonations(),
				$this->getSofortUrlGeneratorConfigForDonations(),
				$this->getSofortClient(),
				$this->getUrlGenerator()->generateAbsoluteUrl( Routes::SHOW_MEMBERSHIP_CONFIRMATION ),
				useLegacyPayPalUrlGenerator: false
			),
			new PaymentProviderAdapterFactoryImplementation(
				$this->getPayPalApiClient(),
				$this->getPaymentAdapterConfigForMemberships(),
				$this->getPaymentRepository()
			)
		);
	}

	private function newPaymentReferenceCodeGenerator(): PaymentReferenceCodeGenerator {
		return new UniquePaymentReferenceCodeGenerator(
			new CharacterPickerPaymentReferenceCodeGenerator( new RandomCharacterIndexGenerator() ),
			$this->getEntityManager()
		);
	}

	private function newDonationMailer(): DonationMailer {
		return new DonationMailer(
			$this->newDonationConfirmationMailer(),
			$this->newAdminMailer(
				'eine Spende',
				'https://backend.wikimedia.de/backend/donation/list',
				$this->getAdminMessenger()
			),
			$this->newGetPaymentUseCase(),
			adminEmailAddress: $this->config['contact-info']['admin']['email']
		);
	}

	private function newDonationUpdatedMailer(): DonationMailer {
		return new DonationMailer(
			$this->newDonationUpdatedTemplateMailer(),
			new NullMailer(),
			$this->newGetPaymentUseCase(),
			adminEmailAddress: $this->config['contact-info']['admin']['email']
		);
	}

	private function newDonationValidator(): AddDonationValidator {
		return new AddDonationValidator(
			$this->getEmailValidator(),
			$this->newAddressValidator()
		);
	}

	public function newDonationPaymentValidator(): DonationPaymentValidator {
		return new DonationPaymentValidator( $this->getPaymentTypesSettings()->getPaymentTypesForDonation() );
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
			$this->newDonationAuthorizationChecker( $updateToken, $accessToken ),
			$this->newUpdateDonorValidator(),
			$this->getDonationRepository(),
			$this->newDonationUpdatedMailer(),
			$this->getDonationEventEmitter()
		);
	}

	private function newUpdateDonorValidator(): UpdateDonorValidator {
		return new UpdateDonorValidator( $this->newAddressValidator(), $this->getEmailValidator() );
	}

	private function newDonationConfirmationMailer(): DonationTemplateMailerInterface {
		return $this->newErrorHandlingTemplateMailer(
			$this->getDonationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Donation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new DonationConfirmationMailSubjectRenderer(
				$this->getMailTranslator(),
				'mail_subject_confirm_donation',
				'mail_subject_confirm_donation_promise'
			)
		);
	}

	private function newDonationUpdatedTemplateMailer(): DonationTemplateMailerInterface {
		return $this->newErrorHandlingTemplateMailer(
			$this->getDonationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Donation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			new BasicMailSubjectRenderer(
				$this->getMailTranslator(),
				'mail_subject_update_donation'
			)
		);
	}

	/**
	 * @return LegacyPayPalURLGeneratorConfig
	 * @deprecated See https://phabricator.wikimedia.org/T329159
	 */
	private function getLegacyPayPalUrlConfigForDonations(): LegacyPayPalURLGeneratorConfig {
		return LegacyPayPalURLGeneratorConfig::newFromConfig(
			array_merge( $this->config['paypal-donation'], [ 'locale' => $this->getLocale() ] ),
			new TranslatablePaymentItemDescription( 'paypal_item_name_donation', $this->getPaymentProviderItemsTranslator() )
		);
	}

	private function getSofortUrlGeneratorConfigForDonations(): SofortURLGeneratorConfig {
		$config = $this->config['sofort'];
		$locale = \Locale::parseLocale( $this->getLocale() );
		$translator = $this->getPaymentProviderItemsTranslator();
		return new SofortURLGeneratorConfig(
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

	private function newCreditCardUrlGeneratorConfig(): CreditCardURLGeneratorConfig {
		$locale = \Locale::parseLocale( $this->getLocale() );
		return CreditCardURLGeneratorConfig::newFromConfig(
			array_merge( $this->config['creditcard'], [ 'locale' => $locale['language'] ] ),
			new TranslatablePaymentItemDescription( 'credit_card_item_name_donation', $this->getPaymentProviderItemsTranslator() )
		);
	}

	public function getDonationRepository(): DonationRepository {
		return $this->createSharedObject( DonationRepository::class, function () {
			return new LoggingDonationRepository(
				new DoctrineDonationRepository(
					$this->getEntityManager(),
					$this->newDonationExistsChecker(),
					$this->newGetPaymentUseCase(),
					$this->getModerationReasonRepositoryForDonation()
				),
				$this->getLogger()
			);
		} );
	}

	/**
	 * This is used to inject fakes for testing
	 *
	 * @param DonationRepository $repository
	 *
	 * @return void
	 */
	public function setDonationRepository( DonationRepository $repository ): void {
		$this->sharedObjects[DonationRepository::class] = $repository;
	}

	public function getPaymentIdRepository(): PaymentIdRepository {
		return $this->createSharedObject( PaymentIdRepository::class, function () {
			return new DoctrinePaymentIdRepository(
				$this->getEntityManager()
			);
		} );
	}

	public function getPaymentRepository(): PaymentRepository&PayPalPaymentIdentifierRepository {
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

	public function newAddCommentUseCase( string $updateToken ): AddCommentUseCase {
		return new AddCommentUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizationChecker( $updateToken ),
			$this->newCommentPolicyValidator(),
			$this->newAddCommentValidator()
		);
	}

	private function newDonationAuthorizationChecker( string $updateToken = '', string $accessToken = '' ): DonationAuthorizationChecker {
		return new AuthorizationChecker( $this->getTokenRepositoryWithLegacyFallback(), $updateToken, $accessToken );
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
			$this->getUrlAuthenticationLoader(),
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
			$this->getUrlGenerator(),
			$this->newGetPaymentUseCase()
		);
	}

	public function newApplyForMembershipUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->getMembershipApplicationRepository(),
			new DoctrineMembershipIdGenerator( $this->getEntityManager() ),
			$this->newMembershipAuthorizer(),
			new MailMembershipApplicationNotifier(
				$this->newApplyForMembershipMailer(),
				$this->newAdminMailer(
					'ein Mitgliedschaftsantrag',
					'https://backend.wikimedia.de/backend/member/list',
					$this->getAdminMessenger()
				),
				$this->newGetPaymentUseCase(),
				adminEmailAddress: $this->config['contact-info']['admin']['email']
			),
			$this->newMembershipApplicationValidator(),
			$this->newApplyForMembershipPolicyValidator(),
			$this->newMembershipApplicationTracker(),
			$this->newMembershipApplicationPiwikTracker(),
			$this->getMembershipEventEmitter(),
			$this->getIncentiveFinder(),
			$this->newPaymentServiceFactory()
		);
	}

	private function newAdminMailer( string $itemType, string $focURL, Messenger $messenger ): TemplateBasedMailer {
		return new TemplateBasedMailer(
			$messenger,
			new TwigTemplate(
				$this->getMailerTwig(),
				'Admin_Moderation.txt.twig',
				[
					'itemType' => $itemType,
					'focURL' => $focURL
				]
			),
			new AdminModerationMailRenderer()
		);
	}

	private function newApplyForMembershipMailer(): MembershipTemplateMailerInterface {
		return $this->newErrorHandlingTemplateMailer(
			$this->getMembershipMessenger(),
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

	private function newApplyForMembershipPolicyValidator(): MembershipModerationService {
		return new MembershipModerationService(
			$this->newTextPolicyValidator( 'fields' ),
			$this->getEmailAddressBlockList()
		);
	}

	private function getMembershipApplicationAuthorizer( string $updateToken = '', string $accessToken = '' ): MembershipAuthorizationChecker {
		return $this->createSharedObject(
			MembershipAuthorizationChecker::class,
			function () use ( $accessToken, $updateToken ): MembershipAuthorizationChecker {
				return new AuthorizationChecker( $this->getTokenRepositoryWithLegacyFallback(), $updateToken, $accessToken );
			}
		);
	}

	public function setMembershipApplicationRepository( ApplicationRepository $applicationRepository ): void {
		$this->sharedObjects[ApplicationRepository::class] = $applicationRepository;
	}

	public function getMembershipApplicationRepository(): ApplicationRepository {
		return $this->createSharedObject( ApplicationRepository::class, function () {
			return new DoctrineApplicationRepository(
				$this->getEntityManager(),
				$this->newGetPaymentUseCase(),
				$this->getModerationReasonRepositoryForMembership()
			);
		} );
	}

	public function setMembershipApplicationAuthorizationChecker( MembershipAuthorizationChecker $authorizer ): void {
		$this->sharedObjects[MembershipAuthorizationChecker::class] = $authorizer;
	}

	public function newMembershipApplicationConfirmationUseCase( ShowApplicationConfirmationPresenter $presenter, string $accessToken ): ShowApplicationConfirmationUseCase {
		return new ShowApplicationConfirmationUseCase(
			$presenter,
			$this->getMembershipApplicationAuthorizer( '', $accessToken ),
			$this->getMembershipApplicationRepository(),
			$this->newGetPaymentUseCase()
		);
	}

	public function newGetDonationUseCase( string $accessToken ): GetDonationUseCase {
		return new GetDonationUseCase(
			$this->newDonationAuthorizationChecker( '', $accessToken ),
			$this->getDonationRepository()
		);
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
				'paymentIntervals' => [
					PaymentInterval::OneTime->value,
					PaymentInterval::Monthly->value,
					PaymentInterval::Quarterly->value,
					PaymentInterval::HalfYearly->value,
					PaymentInterval::Yearly->value,
				],
				'userDataKey' => $this->getUserDataKeyGenerator()->getDailyKey(),
				'countries' => $this->getCountries(),
				'addressValidationPatterns' => $this->getValidationRules()->address,
			]
		);
	}

	private function getMembershipApplicationFormTemplate(): TwigTemplate {
		$validation = $this->getValidationRules();
		$paymentIntervals = $this->getChoiceFactory()->getMembershipPaymentIntervals();
		return $this->getLayoutTemplate( 'Membership_Application.html.twig', [
			'presetAmounts' => $this->getPresetAmountsSettings( 'membership' ),
			'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForMembershipApplication(),
			'paymentIntervals' => $paymentIntervals,
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
		$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/countries.json' );
		return ( json_decode( $json ) )->countries;
	}

	public function getValidationRules(): object {
		$json = ( new SimpleFileFetcher() )->fetchFile( $this->getI18nDirectory() . '/data/validation.json' );
		return json_decode( $json );
	}

	public function newBookDonationUseCase( string $updateToken ): BookDonationUseCase {
		return new BookDonationUseCase(
			$this->getDonationIdRepository(),
			$this->getDonationRepository(),
			$this->newDonationAuthorizationChecker( $updateToken ),
			$this->newDonationMailer(),
			$this->newPaymentBookingService(),
			$this->newDonationEventLogger()
		);
	}

	public function newBookDonationUseCaseForPayPal(): BookDonationUseCase {
		return new BookDonationUseCase(
			$this->getDonationIdRepository(),
			$this->getDonationRepository(),
			new LenientAuthorizationChecker(),
			$this->newDonationMailer(),
			$this->newPaymentBookingService(),
			$this->newDonationEventLogger()
		);
	}

	public function newHandlePaypalPaymentWithoutDonationUseCase(): HandlePaypalPaymentWithoutDonationUseCase {
		return new HandlePaypalPaymentWithoutDonationUseCase(
			$this->newPayPalBookingService(),
			$this->getDonationRepository(),
			$this->getDonationIdRepository(),
			$this->newDonationMailer(),
			$this->newDonationEventLogger()
		);
	}

	public function newMembershipApplicationConfirmationHtmlPresenter(): MembershipApplicationConfirmationHtmlPresenter {
		return new MembershipApplicationConfirmationHtmlPresenter(
			$this->getLayoutTemplate( 'Membership_Application_Confirmation.html.twig' )
		);
	}

	public function newMembershipFormViolationPresenter(): MembershipFormViolationPresenter {
		return new MembershipFormViolationPresenter(
			$this->getMembershipApplicationFormTemplate(),
			$this->newBankDataConverter()
		);
	}

	public function newCreditCardNotificationPresenter(): CreditCardNotificationPresenter {
		return new CreditCardNotificationPresenter(
			$this->config['creditcard']['return-url']
		);
	}

	private function newDonationModerationService(): DonationModerationService {
		return new DonationModerationService(
			$this->newDonationAmountPolicyValidator(),
			$this->newTextPolicyValidator( 'fields' ),
			$this->getEmailAddressBlockList()
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

	public function setConfigCache( CacheInterface $cache ): void {
		$this->sharedObjects['Cache::Config'] = $cache;
	}

	private function getConfigurationCache(): CacheInterface {
		return $this->createSharedObject( 'Cache::Config', static function () {
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
			$this->newDonationAuthorizationChecker( $updateToken ),
			$this->getDonationRepository(),
			$this->newDonationMailer()
		);
	}

	public function newPageNotFoundHtmlPresenter(): PageNotFoundPresenter {
		return new PageNotFoundPresenter( $this->getLayoutTemplate( 'Page_Not_Found.html.twig' ) );
	}

	public function getI18nDirectory(): string {
		return $this->getAbsolutePath( $this->config['i18n-base-path'] ) . '/' . $this->getLocale();
	}

	/**
	 * Return the "root" path of the content repository
	 *
	 * @return string
	 */
	public function getContentPath(): string {
		return dirname( $this->getAbsolutePath( $this->config['i18n-base-path'] ) );
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
			return TwigContentProviderFactory::createContentProvider( new TwigContentProviderConfig(
				$this->getI18nDirectory(),
				$this->config['twig']['enable-cache'] ? $this->getCachePath() . '/content' : null,
				[
					'basepath' => $this->config['web-basepath']
				]
			) );
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
			return new CampaignConfigurationLoader( new SimpleFileFetcher(), $this->getConfigurationCache() );
		} );
	}

	public function setCampaignConfigurationLoader( CampaignConfigurationLoaderInterface $loader ): void {
		$this->sharedObjects[CampaignConfigurationLoaderInterface::class] = $loader;
	}

	private function newCampaignFeatures(): Set {
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

	/**
	 * @return Bucket[]
	 */
	public function getSelectedBuckets(): array {
		// when in the web environment, selected buckets will be set by BucketSelectionServiceProvider during request processing
		// other environments (testing/cli) may set this during setup
		if ( !isset( $this->sharedObjects['selectedBuckets'] ) ) {
			throw new \LogicException( 'Buckets were not selected yet, you must not initialize A/B tested classes before the app processes the request.' );
		}
		return $this->sharedObjects['selectedBuckets'];
	}

	/**
	 * @param Bucket[] $selectedBuckets
	 */
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

	private function getMailTemplateDirectory(): string {
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

	public function newFindCitiesUseCase(): FindCitiesUseCase {
		return new FindCitiesUseCase(
			new DoctrineLocationRepository( $this->getEntityManager() )
		);
	}

	/**
	 * Paths for ORMSetup::createXMLMetadataConfiguration
	 *
	 * @return string[]
	 */
	public function getDoctrineXMLMappingPaths(): array {
		return $this->getBoundedContextFactoryCollection()->getDoctrineXMLMappingPaths();
	}

	/**
	 * @return \Doctrine\Migrations\Tools\Console\Command\DoctrineCommand[]
	 */
	public function newDoctrineMigrationCommands(): array {
		$dependencyFactory = DependencyFactory::fromEntityManager(
			new PhpFile( __DIR__ . '/../../app/config/migrations.php' ),
			new ExistingEntityManager( $this->getEntityManager() )
		);
		return [
			new CurrentCommand( $dependencyFactory ),
			new DumpSchemaCommand( $dependencyFactory ),
			new ExecuteCommand( $dependencyFactory ),
			new GenerateCommand( $dependencyFactory ),
			new LatestCommand( $dependencyFactory ),
			new MigrateCommand( $dependencyFactory ),
			new RollupCommand( $dependencyFactory ),
			new StatusCommand( $dependencyFactory ),
			new VersionCommand( $dependencyFactory ),
			new UpToDateCommand( $dependencyFactory ),
			new SyncMetadataCommand( $dependencyFactory ),
			new ListCommand( $dependencyFactory ),
		];
	}

	public function getLocale(): string {
		if ( !isset( $this->sharedObjects[ 'locale' ] ) ) {
			throw new \LogicException( 'Locale was not selected yet, you must not initialize locale dependant classes before the app processes the request.' );
		}
		return $this->sharedObjects[ 'locale' ];
	}

	public function setLocale( string $locale ): void {
		$this->sharedObjects[ 'locale' ] = $locale;
	}

	public function getRootPath(): string {
		return $this->getAbsolutePath( __DIR__ . '/../..' );
	}

	private function newPaymentBookingService(): PaymentBookingService {
		return new PaymentBookingServiceWithUseCase(
			new BookPaymentUseCase(
				$this->getPaymentRepository(),
				$this->getPaymentIdRepository(),
				$this->getVerificationServiceFactory(),
				$this->newDoctrineTransactionIdFinder()
			)
		);
	}

	private function newPayPalBookingService(): PaypalBookingService {
		return new PaypalBookingServiceWithUseCase(
			new CreateBookedPayPalPaymentUseCase(
				$this->getPaymentRepository(),
				$this->getPaymentIdRepository(),
				$this->getPayPalVerificationService(),
				$this->newDoctrineTransactionIdFinder()
			)
		);
	}

	private function getPayPalVerificationService(): VerificationService {
		return $this->createSharedObject( PayPalVerificationService::class, function (): VerificationService {
			return new PayPalVerificationService(
				new Client(),
				$this->config['paypal-donation']['base-url'],
				$this->config['paypal-donation']['account-address']
			);
		} );
	}

	public function setPayPalVerificationService( VerificationService $payPalVerificationService ): void {
		$this->sharedObjects[PayPalVerificationService::class] = $payPalVerificationService;
	}

	private function getVerificationServiceFactory(): VerificationServiceFactory {
		return $this->createSharedObject( VerificationServiceFactory::class, function (): VerificationServiceFactory {
			return new ExternalVerificationServiceFactory(
				new Client(),
				$this->config['paypal-donation']['base-url'],
				$this->config['paypal-donation']['account-address']
			);
		} );
	}

	public function setVerificationServiceFactory( VerificationServiceFactory $verificationServiceFactory ): void {
		$this->sharedObjects[VerificationServiceFactory::class] = $verificationServiceFactory;
	}

	private function newDoctrineTransactionIdFinder(): DoctrineTransactionIdFinder {
		return new DoctrineTransactionIdFinder( $this->getConnection() );
	}

	public function newPaymentServiceFactory(): PaymentServiceFactory {
		return new PaymentServiceFactory(
			$this->newCreatePaymentUseCaseForMemberships(),
			$this->getPaymentTypesSettings()->getPaymentTypesForMembershipApplication()
		);
	}

	private function newDonationExistsChecker(): DonationExistsChecker {
		return new DoctrineDonationExistsChecker( $this->getEntityManager() );
	}

	private function getDonationIdRepository(): DonationIdRepository {
		return $this->createSharedObject( DonationIdRepository::class, function (): DonationIdRepository {
			return new DoctrineDonationIdRepository( $this->getEntityManager() );
		} );
	}

	public function setPayPalAPI( PaypalAPI $paypalAPI ): void {
		$this->sharedObjects[PaypalAPI::class] = $paypalAPI;
	}

	public function getPayPalApiClient(): PaypalAPI {
		if ( !isset( $this->sharedObjects[PaypalAPI::class] ) ) {
			throw new \LogicException( 'PayPal API was not initialized in environment setup factory!' );
		}
		return $this->sharedObjects[PaypalAPI::class];
	}

	public function getDonationAuthorizer(): DonationAuthorizer {
		return $this->createSharedObject( DonationAuthorizer::class, function (): PersistentAuthorizer {
			return new PersistentAuthorizer(
				$this->getTokenRepository(),
				new RandomTokenGenerator( $this->config['token-length'] ),
				$this->getLogger(),
				new \DateInterval( $this->config['token-validity-timestamp'] )
			);
		} );
	}

	private function getPayPalAdapterConfigForDonations(): PayPalPaymentProviderAdapterConfig {
		return $this->createSharedObject( PayPalPaymentProviderAdapterConfig::class . '::donation', function () {
			$configLoader = new PayPalAdapterConfigLoader( $this->getConfigurationCache() );
			return $configLoader->load(
				$this->getRootPath() . '/' . $this->config[ 'paypal-donation' ][ 'config-path' ],
				'donation',
				$this->getLocale()
			);
		} );
	}

	public function getUrlAuthenticationLoader(): DonationUrlAuthenticationLoader&MembershipUrlAuthenticationLoader {
		return $this->createSharedObject( DonationUrlAuthenticationLoader::class, function (): DonationUrlAuthenticationLoader&MembershipUrlAuthenticationLoader {
			return new AuthenticationLoader(
				$this->getTokenRepositoryWithLegacyFallback()
			);
		} );
	}

	public function getTokenRepository(): TokenRepository {
		return $this->createSharedObject( TokenRepository::class, function (): TokenRepository {
			return new DoctrineTokenRepository( $this->getEntityManager() );
		} );
	}

	private function getTokenRepositoryWithLegacyFallback(): TokenRepository {
		return $this->createSharedObject( TokenRepository::class . '::withLegacyFallback', function (): TokenRepository {
			return new FallbackTokenRepository( $this->getTokenRepository(), $this->getEntityManager() );
		} );
	}

	private function getPaymentAdapterConfigForMemberships(): PayPalPaymentProviderAdapterConfig {
		return $this->createSharedObject( PayPalPaymentProviderAdapterConfig::class . '::membership', function () {
			$configLoader = new PayPalAdapterConfigLoader( $this->getConfigurationCache() );
			return $configLoader->load(
				$this->getRootPath() . '/' . $this->config[ 'paypal-membership' ][ 'config-path' ],
				'membership',
				$this->getLocale()
			);
		} );
	}

	private function newMembershipAuthorizer(): MembershipAuthorizer {
		return new PersistentAuthorizer(
			$this->getTokenRepository(),
			new RandomTokenGenerator( $this->config['token-length'] ),
			$this->getLogger(),
			new \DateInterval( $this->config['token-validity-timestamp'] )
		);
	}

	/**
	 * @return string[]
	 */
	private function getEmailAddressBlockList(): array {
		$blockList = $this->config['email-address-blocklist'] ?? [];
		// Backwards compatibility check for legacy config key
		// Remove this check (and maybe inline this method) once
		// we have changed all configuration files (also in the infrastructure repo)
		// see https://phabricator.wikimedia.org/T352788
		if ( $blockList === [] ) {
			$blockList = $this->config['email-address-blacklist'] ?? [];
		}
		return $blockList;
	}

	/**
	 * @deprecated Should be removed after the 2023/2024 thank you campaign
	 */
	public function getMembershipImpressionCounter(): MembershipImpressionCounter {
		return $this->createSharedObject( MembershipImpressionCounter::class, static function (): MembershipImpressionCounter {
			return new NullMembershipImpressionCounter();
		} );
	}

	/**
	 * @deprecated Should be removed after the 2023/2024 thank you campaign
	 */
	public function setMembershipImpressionCounter( MembershipImpressionCounter $membershipImpressionCounter ): void {
		$this->sharedObjects[MembershipImpressionCounter::class] = $membershipImpressionCounter;
	}

}
