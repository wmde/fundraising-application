<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use FileFetcher\ErrorLoggingFileFetcher;
use FileFetcher\SimpleFileFetcher;
use GuzzleHttp\Client;
use NumberFormatter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RemotelyLiving\Doorkeeper\Doorkeeper;
use RemotelyLiving\Doorkeeper\Features\Set;
use RemotelyLiving\Doorkeeper\Requestor;
use Swift_MailTransport;
use Swift_NullTransport;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint as ValidatorConstraint;
use Symfony\Component\Validator\Constraints\Range as RangeConstraint;
use Symfony\Component\Validator\Constraints\Required as RequiredConstraint;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;
use TNvpServiceDispatcher;
use Twig_Environment;
use Twig_Extensions_Extension_Intl;
use Twig_SimpleFunction;
use WMDE\Clock\SystemClock;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Euro\Euro;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\DonationContext\Authorization\RandomTokenGenerator;
use WMDE\Fundraising\DonationContext\Authorization\TokenGenerator;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineCommentFinder;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationAuthorizer;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationEventLogger;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationPrePersistSubscriber;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineDonationTokenFetcher;
use WMDE\Fundraising\DonationContext\DataAccess\UniqueTransferCodeGenerator;
use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\DonationContext\Domain\Validation\DonorValidator;
use WMDE\Fundraising\DonationContext\DonationAcceptedEventHandler;
use WMDE\Fundraising\DonationContext\Infrastructure\BestEffortDonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingCommentFinder;
use WMDE\Fundraising\DonationContext\Infrastructure\LoggingDonationRepository;
use WMDE\Fundraising\DonationContext\Infrastructure\TemplateMailerInterface as DonationTemplateMailerInterface;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationPolicyValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationValidator;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\InitialDonationStatusPicker;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\ReferrerGeneralizer;
use WMDE\Fundraising\DonationContext\UseCases\CancelDonation\CancelDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\CreditCardPaymentNotification\CreditCardNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentCompletionNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorUseCase;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorValidator;
use WMDE\Fundraising\DonationContext\UseCases\ValidateDonor\ValidateDonorUseCase;
use WMDE\Fundraising\Frontend\BucketTesting\BucketSelector;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignBuilder;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignFeatureBuilder;
use WMDE\Fundraising\Frontend\BucketTesting\DoorkeeperFeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BestEffortBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\JsonBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamLogWriter;
use WMDE\Fundraising\Frontend\BucketTesting\RandomBucketSelection;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\AllOfTheCachePurger;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\AuthorizedCachePurger;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;
use WMDE\Fundraising\Frontend\Infrastructure\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Infrastructure\JsonStringReader;
use WMDE\Fundraising\Frontend\Infrastructure\LoggingMailer;
use WMDE\Fundraising\Frontend\Infrastructure\MailTemplateFilenameTraversable;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\OperatorMailer;
use WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\KontoCheckBankDataGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\KontoCheckIbanValidator;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\McpCreditCardService;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikServerSideTracker;
use WMDE\Fundraising\Frontend\Infrastructure\ProfilerDataCollector;
use WMDE\Fundraising\Frontend\Infrastructure\ServerSideTracker;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;
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
use WMDE\Fundraising\Frontend\Presentation\Presenters\CreditCardPaymentUrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormViolationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonorUpdateHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\GetInTouchHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipFormViolationPresenter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\PageNotFoundPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;
use WMDE\Fundraising\Frontend\Validation\TemplateNameValidator;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipTokenGenerator;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationTokenFetcher;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationTracker;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineMembershipApplicationPrePersistSubscriber;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\MembershipContext\Infrastructure\LoggingApplicationRepository;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\MembershipContext\Tracking\ApplicationTracker;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipPolicyValidator;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\HandleSubscriptionPaymentNotification\HandleSubscriptionPaymentNotificationUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\HandleSubscriptionSignupNotification\HandleSubscriptionSignupNotificationUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationUseCase;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateMembershipFeeUseCase;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\Client as SofortClient;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\BankDataValidator;
use WMDE\Fundraising\PaymentContext\Domain\DefaultPaymentDelayCalculator;
use WMDE\Fundraising\PaymentContext\Domain\IbanBlocklist;
use WMDE\Fundraising\PaymentContext\Domain\LessSimpleTransferCodeGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDataValidator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\CreditCard as CreditCardUrlGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\CreditCardConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPal as PayPalUrlGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPalConfig;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\Sofort as SofortUrlGenerator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\SofortConfig;
use WMDE\Fundraising\PaymentContext\Domain\TransferCodeGenerator;
use WMDE\Fundraising\PaymentContext\Infrastructure\CreditCardService;
use WMDE\Fundraising\PaymentContext\UseCases\CheckIban\CheckIbanUseCase;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanUseCase;
use WMDE\Fundraising\Store\Factory as StoreFactory;
use WMDE\Fundraising\Store\Installer;
use WMDE\Fundraising\SubscriptionContext\DataAccess\DoctrineSubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\LoggingSubscriptionRepository;
use WMDE\Fundraising\SubscriptionContext\Infrastructure\TemplateMailerInterface as SubscriptionTemplateMailerInterface;
use WMDE\Fundraising\SubscriptionContext\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\SubscriptionContext\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase;
use WMDE\Fundraising\SubscriptionContext\Validation\SubscriptionDuplicateValidator;
use WMDE\Fundraising\SubscriptionContext\Validation\SubscriptionValidator;
use WMDE\FunValidators\Validators\AllowedValuesValidator;
use WMDE\FunValidators\Validators\AmountPolicyValidator;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\TextPolicyValidator;

/**
 * @licence GNU GPL v2+
 */
class FunFunFactory implements ServiceProviderInterface {

	private $config;

	/**
	 * @var Container
	 */
	private $pimple;

	private $addDoctrineSubscribers = true;

	private $sharedObjects;

	/**
	 * @var Stopwatch|null
	 */
	private $profiler = null;

	public function __construct( array $config ) {
		$this->config = $config;
		$this->pimple = $this->newPimple();
		$this->sharedObjects = [];
	}

	private function newPimple(): Container {
		$container = new Container();
		$container->register(
			new MembershipContextFactory(
				[
					// Explicitly passing redundantly - repeated use could be a case for config parameters
					// http://symfony.com/doc/current/service_container/parameters.html#parameters-in-configuration-files
					'token-length' => $this->config['token-length'],
					'token-validity-timestamp' => $this->config['token-validity-timestamp']
				]
			)
		);
		$this->register( $container );
		return $container;
	}

	public function register( Container $container ): void {
		$container['logger'] = function() {
			return new NullLogger();
		};

		$container['paypal_logger'] = function() {
			return new NullLogger();
		};

		$container['sofort_logger'] = function() {
			return new NullLogger();
		};

		$container['profiler_data_collector'] = function() {
			return new ProfilerDataCollector();
		};

		$container['dbal_connection'] = function() {
			return DriverManager::getConnection( $this->config['db'] );
		};

		$container['entity_manager'] = function() {
			$entityManager = ( new StoreFactory( $this->getConnection(), $this->getVarPath() . '/doctrine_proxies' ) )
				->getEntityManager();
			if ( $this->addDoctrineSubscribers ) {
				$entityManager->getEventManager()->addEventSubscriber( $this->newDoctrineDonationPrePersistSubscriber() );
				$entityManager->getEventManager()->addEventSubscriber( $this->newDoctrineMembershipApplicationPrePersistSubscriber() );
			}

			return $entityManager;
		};

		$container['subscription_repository'] = function() {
			return new LoggingSubscriptionRepository(
				new DoctrineSubscriptionRepository( $this->getEntityManager() ),
				$this->getLogger()
			);
		};

		$container['donation_repository'] = function() {
			return new LoggingDonationRepository(
				new DoctrineDonationRepository( $this->getEntityManager() ),
				$this->getLogger()
			);
		};

		$container['membership_application_repository'] = function() {
			return new LoggingApplicationRepository(
				new DoctrineApplicationRepository( $this->getEntityManager() ),
				$this->getLogger()
			);
		};

		$container['comment_repository'] = function() {
			return new LoggingCommentFinder(
				new DoctrineCommentFinder( $this->getEntityManager() ),
				$this->getLogger()
			);
		};

		$container['mail_validator'] = function() {
			return new EmailValidator( new InternetDomainNameValidator() );
		};

		$container['subscription_validator'] = function() {
			return new SubscriptionValidator(
				$this->getEmailValidator(),
				$this->newTextPolicyValidator( 'fields' ),
				$this->newSubscriptionDuplicateValidator(),
				$this->newHonorificValidator()
			);
		};

		$container['template_name_validator'] = function() {
			return new TemplateNameValidator( $this->getSkinTwig() );
		};

		$container['contact_validator'] = function() {
			return new GetInTouchValidator( $this->getEmailValidator() );
		};

		$container['greeting_generator'] = function() {
			return new GreetingGenerator();
		};

		$container['translator'] = function() {
			$translationFactory = new TranslationFactory();
			$loaders = [
				'json' => $translationFactory->newJsonLoader()
			];
			$locale = $this->config['locale'];
			$messagesPath = $this->getI18nDirectory() . $this->config['translation']['message-dir'];
			$translator = $translationFactory->create( $loaders, $locale );
			foreach ($this->config['translation']['files'] as $domain => $file) {
				$translator->addResource( 'json', $messagesPath . '/' . $file, $locale, $domain );
			}

			return $translator;
		};

		// In the future, this could be locale-specific or filled from a DB table
		$container['honorifics'] = function() {
			return new Honorifics( [
				'' => 'Kein Titel',
				'Dr.' => 'Dr.',
				'Prof.' => 'Prof.',
				'Prof. Dr.' => 'Prof. Dr.'
			] );
		};

		$container['twig'] = function() {
			$config = $this->config['twig'];
			$config['loaders']['filesystem']['template-dir'] = $this->getSkinDirectory();

			$twigFactory = $this->newTwigFactory( $config );
			$configurator = $twigFactory->newTwigEnvironmentConfigurator();

			$loaders = array_filter( [
				$twigFactory->newFileSystemLoader(),
				$twigFactory->newArrayLoader(), // This is just a fallback for testing
			] );
			$extensions = [
				$twigFactory->newTranslationExtension( $this->getTranslator() ),
				new Twig_Extensions_Extension_Intl()
			];
			$filters = [
				$twigFactory->newFilePrefixFilter(
					$this->getFilePrefixer()
				)
			];
			$functions = [
				new Twig_SimpleFunction(
					'web_content',
					function( string $name, array $context = [] ): string {
						return $this->getContentProvider()->getWeb( $name, $context );
					},
					[ 'is_safe' => [ 'html' ] ]
				),
			];

			return $configurator->getEnvironment( $this->pimple['skin_twig_environment'], $loaders, $extensions, $filters, $functions );
		};

		$container['mailer_twig'] = function() {
			$twigFactory = $this->newTwigFactory( $this->config['mailer-twig'] );
			$configurator = $twigFactory->newTwigEnvironmentConfigurator();

			$loaders = array_filter( [
				$twigFactory->newFileSystemLoader(),
				$twigFactory->newArrayLoader(), // This is just a fallback for testing
			] );
			$extensions = [
				$twigFactory->newTranslationExtension( $this->getTranslator() ),
				new Twig_Extensions_Extension_Intl(),
			];
			$filters = [];
			$functions = [
				new Twig_SimpleFunction(
					'mail_content',
					function( string $name, array $context = [] ): string {
						return $this->getContentProvider()->getMail( $name, $context );
					},
					[ 'is_safe' => [ 'all' ] ]
				),
				new Twig_SimpleFunction(
					'url',
					function( string $name, array $parameters = [] ): string {
						return $this->getUrlGenerator()->generateAbsoluteUrl( $name, $parameters );
					}
				)
			];

			$twigEnvironment = new Twig_Environment();

			return $configurator->getEnvironment( $twigEnvironment, $loaders, $extensions, $filters, $functions );
		};

		$container['messenger_suborganization'] = function() {
			return new Messenger(
				new Swift_MailTransport(),
				$this->getSubOrganizationEmailAddress(),
				$this->config['contact-info']['suborganization']['name']
			);
		};

		$container['messenger_organization'] = function() {
			return new Messenger(
				new Swift_MailTransport(),
				$this->getOrganizationEmailAddress(),
				$this->config['contact-info']['organization']['name']
			);
		};

		$container['paypal-payment-notification-verifier'] = function() {
			return new LoggingPaymentNotificationVerifier(
				new PayPalPaymentNotificationVerifier(
					new Client(),
					$this->config['paypal-donation']['base-url'],
					$this->config['paypal-donation']['account-address']
				),
				$this->getLogger()
			);
		};

		$container['paypal-membership-fee-notification-verifier'] = function() {
			return new LoggingPaymentNotificationVerifier(
				new PayPalPaymentNotificationVerifier(
					new Client(),
					$this->config['paypal-membership']['base-url'],
					$this->config['paypal-membership']['account-address']
				),
				$this->getLogger()
			);
		};

		$container['credit-card-api-service'] = function() {
			return new McpCreditCardService(
				new TNvpServiceDispatcher(
					'IMcpCreditcardService_v1_5',
					'https://sipg.micropayment.de/public/creditcard/v1.5/nvp/'
				),
				$this->config['creditcard']['access-key'],
				$this->config['creditcard']['testmode']
			);
		};

		$container['donation_token_generator'] = function() {
			return new RandomTokenGenerator(
				$this->config['token-length'],
				new \DateInterval( $this->config['token-validity-timestamp'] )
			);
		};

		$container['page_cache'] = function() {
			return new VoidCache();
		};

		$container['rendered_page_cache'] = function() {
			return new VoidCache();
		};

		$container['campaign_cache'] = function() {
			return new VoidCache();
		};

		$container['page_view_tracker'] = function () {
			return new PageViewTracker( $this->newServerSideTracker(), $this->config['piwik']['siteUrlBase'] );
		};

		$container['cachebusting_fileprefixer'] = function () {
			return new FilePrefixer( $this->getFilePrefix() );
		};

		$container['content_page_selector'] = function () {
			$json = (new SimpleFileFetcher())->fetchFile( $this->getI18nDirectory() . '/data/pages.json' );
			$config = json_decode( $json, true ) ?? [];

			return new PageSelector( $config );
		};

		$container['content_provider'] = function () {
			return new ContentProvider( [
				'content_path' => $this->getI18nDirectory(),
				'cache' => $this->config['twig']['enable-cache'] ? $this->getCachePath() . '/content' : false,
				'globals' => [
					'basepath' => $this->config['web-basepath']
				]
			] );
		};

		$container['payment-delay-calculator'] = function() {
			return new DefaultPaymentDelayCalculator( $this->getPaymentDelayInDays() );
		};

		$container['sofort-client'] = function () {
			$config = $this->config['sofort'];
			return new SofortClient( $config['config-key'] );
		};

		$container['cookie-builder'] = function (): CookieBuilder {
			return new CookieBuilder(
				$this->config['cookie']['expiration'],
				$this->config['cookie']['path'],
				$this->config['cookie']['domain'],
				$this->config['cookie']['secure'],
				$this->config['cookie']['httpOnly'],
				$this->config['cookie']['raw'],
				$this->config['cookie']['sameSite']
			);
		};

		$container['payment-types-settings'] = function (): PaymentTypesSettings {
			return new PaymentTypesSettings( $this->config['payment-types'] );
		};
	}

	private function createSharedObject( string $id, callable $constructionFunction ) { // @codingStandardsIgnoreLine
		if ( !isset( $this->sharedObjects[$id] ) ) {
			$this->sharedObjects[$id] = $constructionFunction();
		}
		return $this->sharedObjects[$id];
	}

	public function getConnection(): Connection {
		return $this->pimple['dbal_connection'];
	}

	public function getEntityManager(): EntityManager {
		return $this->pimple['entity_manager'];
	}

	private function newDonationEventLogger(): DonationEventLogger {
		return new BestEffortDonationEventLogger(
			new DoctrineDonationEventLogger( $this->getEntityManager() ),
			$this->getLogger()
		);
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
			$this->getSkinTwig(),
			'Comment_List.rss.twig'
		) );
	}

	public function newCommentListHtmlPresenter(): CommentListHtmlPresenter {
		return new CommentListHtmlPresenter( $this->getLayoutTemplate( 'Comment_List.html.twig', [ 'piwikGoals' => [ 1 ] ] ) );
	}

	private function getCommentFinder(): CommentFinder {
		return $this->pimple['comment_repository'];
	}

	public function getSubscriptionRepository(): SubscriptionRepository {
		return $this->pimple['subscription_repository'];
	}

	public function setSubscriptionRepository( SubscriptionRepository $subscriptionRepository ): void {
		$this->pimple['subscription_repository'] = $subscriptionRepository;
	}

	private function getSubscriptionValidator(): SubscriptionValidator {
		return $this->pimple['subscription_validator'];
	}

	public function getEmailValidator(): EmailValidator {
		return $this->pimple['mail_validator'];
	}

	public function getTemplateNameValidator(): TemplateNameValidator {
		return $this->pimple['template_name_validator'];
	}

	public function newAddSubscriptionHtmlPresenter(): AddSubscriptionHtmlPresenter {
		return new AddSubscriptionHtmlPresenter( $this->getLayoutTemplate( 'Subscription_Form.html.twig' ), $this->getTranslator() );
	}

	public function newConfirmSubscriptionHtmlPresenter(): ConfirmSubscriptionHtmlPresenter {
		return new ConfirmSubscriptionHtmlPresenter(
			$this->getLayoutTemplate( 'Confirm_Subscription.twig' ),
			$this->getTranslator()
		);
	}

	public function newAddSubscriptionJsonPresenter(): AddSubscriptionJsonPresenter {
		return new AddSubscriptionJsonPresenter( $this->getTranslator() );
	}

	public function newGetInTouchHtmlPresenter(): GetInTouchHtmlPresenter {
		return new GetInTouchHtmlPresenter(
			$this->getLayoutTemplate( 'contact_form.html.twig' ),
			$this->getTranslator(),
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
			$this->getI18nDirectory() . '/data/useOfFunds.json',
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

	public function getFaqMessages(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/messages/faqMessages.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function getMessages(): string {
		return ( new JsonStringReader(
			$this->getI18nDirectory() . '/messages/messages.json',
			new SimpleFileFetcher()
		) )->readAndValidateJson();
	}

	public function setSkinTwigEnvironment( Twig_Environment $twig ): void {
		$this->pimple['skin_twig_environment'] = $twig;
	}

	public function getSkinTwig(): Twig_Environment {
		return $this->pimple['twig'];
	}

	public function getMailerTwig(): Twig_Environment {
		return $this->pimple['mailer_twig'];
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
		$urlGenerator = $this->getUrlGenerator();
		return [
			'honorifics' => $this->getHonorifics()->getList(),
			'piwik' => $this->config['piwik'],
			'locale' => $this->config['locale'],
			'main_css' => $this->getChoiceFactory()->getMainCss(),
			'main_menu' => [
				[
					'url' => $urlGenerator->generateRelativeUrl( 'list-comments.html' ),
					'id' => 'comments-list',
					'label' => 'menu_item_donation_comments'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'faq' ),
					'id' => 'faq',
					'label' => 'menu_item_faq'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'use-of-funds' ),
					'id' => 'use_of_resources',
					'label' => 'menu_item_use_of_resources'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'page', ['pageName' => 'Spendenquittung'] ),
					'id' => 'donation_receipt',
					'label' => 'menu_item_donation_receipt'
				],
			]
		];
	}

	private function newReferrerGeneralizer(): ReferrerGeneralizer {
		return new ReferrerGeneralizer(
			$this->config['referrer-generalization']['default'],
			$this->config['referrer-generalization']['domain-map']
		);
	}

	public function getLogger(): LoggerInterface {
		return $this->pimple['logger'];
	}

	public function getPaypalLogger(): LoggerInterface {
		return $this->pimple['paypal_logger'];
	}

	public function getSofortLogger(): LoggerInterface {
		return $this->pimple['sofort_logger'];
	}

	private function getVarPath(): string {
		return __DIR__ . '/../../var';
	}

	public function getCachePath(): string {
		return $this->getVarPath() . '/cache';
	}

	public function getLoggingPath(): string {
		return $this->getVarPath() . '/log';
	}

	private function getSharedResourcesPath(): string {
		return $this->getVarPath() . '/shared';
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
			$this->getSuborganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Subscription_Request.txt.twig',
				[
					'greeting_generator' => $this->getGreetingGenerator()
				]
			),
			'mail_subject_subscription'
		);
	}

	private function newConfirmSubscriptionMailer(): SubscriptionTemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSuborganizationMessenger(),
			new TwigTemplate(
					$this->getMailerTwig(),
					'Subscription_Confirmation.txt.twig',
					[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			'mail_subject_subscription_confirmed'
		);
	}

	/**
	 * Create a new TemplateMailer instance
	 *
	 * There is decoration going on, so explicitly hinting what we return (Robustness principle) would be confusing
	 * (you'd expect a TemplateBasedMailer, not a LoggingMailer), so we hint the interface instead.
	 */
	private function newTemplateMailer( Messenger $messenger, TwigTemplate $template, string $messageKey ): LoggingMailer {
		$mailer = new TemplateBasedMailer(
			$messenger,
			$template,
			$this->getTranslator()->trans( $messageKey )
		);

		return new LoggingMailer( $mailer, $this->getLogger() );
	}

	public function getGreetingGenerator(): GreetingGenerator {
		return $this->pimple['greeting_generator'];
	}

	public function newCheckIbanUseCase(): CheckIbanUseCase {
		return new CheckIbanUseCase(
			$this->newBankDataConverter(),
			$this->newIbanValidator(),
			$this->newIbanBlockList()
		);
	}

	public function newGenerateIbanUseCase(): GenerateIbanUseCase {
		return new GenerateIbanUseCase( $this->newBankDataConverter(), $this->newIbanBlockList() );
	}

	public function newIbanPresenter(): IbanPresenter {
		return new IbanPresenter();
	}

	public function newBankDataConverter(): BankDataGenerator {
		return new KontoCheckBankDataGenerator( $this->newIbanValidator() );
	}

	public function setSubscriptionValidator( SubscriptionValidator $subscriptionValidator ): void {
		$this->pimple['subscription_validator'] = $subscriptionValidator;
	}

	public function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->getContactValidator(),
			$this->newContactOperatorMailer(),
			$this->newContactUserMailer()
		);
	}

	private function newContactUserMailer(): TemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSuborganizationMessenger(),
			new TwigTemplate( $this->getMailerTwig(), 'Contact_Confirm_to_User.txt.twig' ),
			'mail_subject_getintouch'
		);
	}

	private function newContactOperatorMailer(): OperatorMailer {
		return new OperatorMailer(
			$this->getSuborganizationMessenger(),
			new TwigTemplate( $this->getMailerTwig(), 'Contact_Forward_to_Operator.txt.twig' )
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

	public function newAuthorizedCachePurger(): AuthorizedCachePurger {
		return new AuthorizedCachePurger(
			new AllOfTheCachePurger(
				$this->getSkinTwig(),
				$this->getPageCache(),
				$this->getRenderedPageCache(),
				$this->getCampaignCache()
			),
			$this->config['purging-secret']
		);
	}

	private function newBankDataValidator(): BankDataValidator {
		return new BankDataValidator( $this->newIbanValidator() );
	}

	private function getSuborganizationMessenger(): Messenger {
		return $this->pimple['messenger_suborganization'];
	}

	public function setSuborganizationMessenger( Messenger $messenger ): void {
		$this->pimple['messenger_suborganization'] = $messenger;
	}

	private function getOrganizationMessenger(): Messenger {
		return $this->pimple['messenger_organization'];
	}

	public function setOrganizationMessenger( Messenger $messenger ): void {
		$this->pimple['messenger_organization'] = $messenger;
	}

	public function setNullMessenger(): void {
		$this->setSuborganizationMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$this->getSubOrganizationEmailAddress()
		) );
		$this->setOrganizationMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$this->getOrganizationEmailAddress()
		) );
	}

	public function getSubOrganizationEmailAddress(): EmailAddress {
		return new EmailAddress( $this->config['contact-info']['suborganization']['email'] );
	}

	public function getOrganizationEmailAddress(): EmailAddress {
		return new EmailAddress( $this->config['contact-info']['organization']['email'] );
	}

	public function newInternalErrorHtmlPresenter(): InternalErrorHtmlPresenter {
		return new InternalErrorHtmlPresenter( $this->getLayoutTemplate( 'Error_Page.html.twig' ) );
	}

	public function newAccessDeniedHtmlPresenter(): InternalErrorHtmlPresenter {
		return new InternalErrorHtmlPresenter( $this->getLayoutTemplate( 'Access_Denied.twig' ) );
	}

	public function getTranslator(): TranslatorInterface {
		return $this->pimple['translator'];
	}

	public function setTranslator( TranslatorInterface $translator ): void {
		$this->pimple['translator'] = $translator;
	}

	private function newTwigFactory( array $twigConfig ): TwigFactory {
		return new TwigFactory(
			array_merge_recursive(
				$twigConfig,
				[ 'web-basepath' => $this->config['web-basepath'] ]
			),
			$this->getCachePath() . '/twig',
			$this->config['locale']
		);
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
			$this->newDonationEventLogger()
		);
	}

	private function newCancelDonationMailer(): DonationTemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getSuborganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Donation_Cancellation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			'mail_subject_confirm_cancellation'
		);
	}

	public function newAddDonationUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->getDonationRepository(),
			$this->newDonationValidator(),
			$this->newDonationPolicyValidator(),
			$this->newReferrerGeneralizer(),
			$this->newDonationConfirmationMailer(),
			$this->newBankTransferCodeGenerator(),
			$this->newDonationTokenFetcher(),
			new InitialDonationStatusPicker()
		);
	}

	private function newBankTransferCodeGenerator(): TransferCodeGenerator {
		return new UniqueTransferCodeGenerator(
			LessSimpleTransferCodeGenerator::newRandomGenerator(),
			$this->getEntityManager()
		);
	}

	private function newDonationValidator(): AddDonationValidator {
		return new AddDonationValidator(
			$this->newPaymentDataValidator(),
			$this->newBankDataValidator(),
			$this->newIbanBlockList(),
			$this->getEmailValidator()
		);
	}

	public function newValidateDonorUseCase(): ValidateDonorUseCase {
		return new ValidateDonorUseCase(
			$this->getEmailValidator()
		);
	}

	public function newUpdateDonorUseCase( string $updateToken, string $accessToken ): UpdateDonorUseCase {
		return new UpdateDonorUseCase(
			$this->newDonationAuthorizer( $updateToken, $accessToken ),
			$this->newUpdateDonorValidator(),
			$this->getDonationRepository(),
			$this->newDonationConfirmationMailer()
		);
	}

	private function newUpdateDonorValidator(): UpdateDonorValidator {
		return new UpdateDonorValidator( new DonorValidator( $this->getEmailValidator() ) );
	}

	private function newDonationConfirmationMailer(): DonationConfirmationMailer {
		return new DonationConfirmationMailer(
			$this->newTemplateMailer(
				$this->getSuborganizationMessenger(),
				new TwigTemplate(
					$this->getMailerTwig(),
					'Donation_Confirmation.txt.twig',
					[
						'greeting_generator' => $this->getGreetingGenerator()
					]
				),
				'mail_subject_confirm_donation'
			)
		);
	}

	public function newPayPalUrlGeneratorForDonations(): PayPalUrlGenerator {
		return new PayPalUrlGenerator(
			$this->getPayPalUrlConfigForDonations(),
			$this->getTranslator()->trans( 'item_name_donation' )
		);
	}

	public function newPayPalUrlGeneratorForMembershipApplications(): PayPalUrlGenerator {
		return new PayPalUrlGenerator(
			$this->getPayPalUrlConfigForMembershipApplications(),
			$this->getTranslator()->trans( 'item_name_membership' )
		);
	}

	private function getPayPalUrlConfigForDonations(): PayPalConfig {
		return PayPalConfig::newFromConfig( $this->config['paypal-donation'] );
	}

	private function getPayPalUrlConfigForMembershipApplications(): PayPalConfig {
		return PayPalConfig::newFromConfig( $this->config['paypal-membership'] );
	}

	public function newSofortUrlGeneratorForDonations(): SofortUrlGenerator {
		$config = $this->config['sofort'];

		return new SofortUrlGenerator(
			new SofortConfig(
				$this->getTranslator()->trans( 'item_name_donation', [], 'messages' ),
				$config['return-url'],
				$config['cancel-url'],
				$config['notification-url']
			),
			$this->getSofortClient()
		);
	}

	public function setSofortClient( SofortClient $client ): void {
		$this->pimple['sofort-client'] = $client;
	}

	private function getSofortClient(): SofortClient {
		return $this->pimple['sofort-client'];
	}

	private function newCreditCardUrlGenerator(): CreditCardUrlGenerator {
		return new CreditCardUrlGenerator( $this->newCreditCardUrlConfig() );
	}

	private function newCreditCardUrlConfig(): CreditCardConfig {
		return CreditCardConfig::newFromConfig( $this->config['creditcard'] );
	}

	public function getDonationRepository(): DonationRepository {
		return $this->pimple['donation_repository'];
	}

	public function newPaymentDataValidator(): PaymentDataValidator {
		return new PaymentDataValidator(
			$this->config['donation-minimum-amount'],
			$this->config['donation-maximum-amount'],
			$this->getPaymentTypesSettings()->getEnabledForDonation()
		);
	}

	private function newAmountFormatter(): AmountFormatter {
		return new AmountFormatter( $this->config['locale'] );
	}

	public function newDecimalNumberFormatter(): NumberFormatter {
		return new NumberFormatter( $this->config['locale'], NumberFormatter::DECIMAL );
	}

	public function newAddCommentUseCase( string $updateToken ): AddCommentUseCase {
		return new AddCommentUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newCommentPolicyValidator(),
			$this->newAddCommentValidator()
		);
	}

	private function newDonationAuthorizer( string $updateToken = null, string $accessToken = null ): DonationAuthorizer {
		return new DoctrineDonationAuthorizer(
			$this->getEntityManager(),
			$updateToken,
			$accessToken
		);
	}

	public function getDonationTokenGenerator(): TokenGenerator {
		return $this->pimple['donation_token_generator'];
	}

	public function getMembershipTokenGenerator(): MembershipTokenGenerator {
		return $this->pimple['fundraising.membership.application.token_generator'];
	}

	public function newDonationConfirmationPresenter(): DonationConfirmationHtmlPresenter {
		return new DonationConfirmationHtmlPresenter(
			new TwigTemplate(
				$this->getSkinTwig(), 'Donation_Confirmation.html.twig',
				array_merge(
					$this->getDefaultTwigVariables(),
					[
						'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForMembershipApplication(),
						'featureToggle' => [
							'donorUpdateEnabled' => $this->getChoiceFactory()->isDonationAddressOptional(),
							'callToActionTemplate' => $this->getChoiceFactory()->getMembershipCallToActionTemplate()
						],
					]
				)
			),
			$this->getUrlGenerator()
		);
	}

	public function newDonorUpdatePresenter(): DonorUpdateHtmlPresenter {
		return new DonorUpdateHtmlPresenter(
			new TwigTemplate(
				$this->getSkinTwig(), 'Donation_Confirmation.html.twig',
				array_merge(
					$this->getDefaultTwigVariables(),
					[
						'featureToggle' => [ 'callToActionTemplate' => $this->getChoiceFactory()->getMembershipCallToActionTemplate() ]
					]
				)
			),
			$this->getUrlGenerator()
		);
	}

	public function newCreditCardPaymentUrlGenerator(): CreditCardPaymentUrlGenerator {
		return new CreditCardPaymentUrlGenerator(
			$this->getTranslator(),
			$this->newCreditCardUrlGenerator()
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
			$this->newApplyForMembershipMailer(),
			$this->newMembershipApplicationValidator(),
			$this->newApplyForMembershipPolicyValidator(),
			$this->newMembershipApplicationTracker(),
			$this->newMembershipApplicationPiwikTracker(),
			$this->getPaymentDelayCalculator()
		);
	}

	private function newApplyForMembershipMailer(): TemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Membership_Application_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			'mail_subject_confirm_membership_application'
		);
	}

	private function newMembershipApplicationValidator(): MembershipApplicationValidator {
		return new MembershipApplicationValidator(
			new ValidateMembershipFeeUseCase(),
			$this->newBankDataValidator(),
			$this->newIbanBlockList(),
			$this->getEmailValidator()
		);
	}

	private function newMembershipApplicationTracker(): ApplicationTracker {
		return new DoctrineApplicationTracker( $this->getEntityManager() );
	}

	private function newMembershipApplicationPiwikTracker(): ApplicationPiwikTracker {
		return new DoctrineApplicationPiwikTracker( $this->getEntityManager() );
	}

	private function getPaymentDelayCalculator(): PaymentDelayCalculator {
		return $this->pimple['payment-delay-calculator'];
	}

	public function getPaymentDelayInDays(): int {
		return $this->getPayPalUrlConfigForMembershipApplications()->getDelayInDays();
	}

	public function setPaymentDelayCalculator( PaymentDelayCalculator $paymentDelayCalculator ): void {
		$this->pimple['payment-delay-calculator'] = $paymentDelayCalculator;
	}

	private function newApplyForMembershipPolicyValidator(): ApplyForMembershipPolicyValidator {
		return new ApplyForMembershipPolicyValidator(
			$this->newTextPolicyValidator( 'fields' ),
			$this->config['email-address-blacklist']
		);
	}

	public function newCancelMembershipApplicationUseCase( string $updateToken ): CancelMembershipApplicationUseCase {
		return new CancelMembershipApplicationUseCase(
			$this->newMembershipApplicationAuthorizer( $updateToken ),
			$this->getMembershipApplicationRepository(),
			$this->newCancelMembershipApplicationMailer()
		);
	}

	private function newMembershipApplicationAuthorizer(
		string $updateToken = null, string $accessToken = null ): ApplicationAuthorizer {

		$this->pimple['fundraising.membership.application.authorizer.update_token'] = $updateToken;
		$this->pimple['fundraising.membership.application.authorizer.access_token'] = $accessToken;
		return $this->pimple['fundraising.membership.application.authorizer'];
	}

	public function setMembershipApplicationRepository( ApplicationRepository $applicationRepository ): void {
		$this->pimple['membership_application_repository'] = $applicationRepository;
	}

	public function getMembershipApplicationRepository(): ApplicationRepository {
		return $this->pimple['membership_application_repository'];
	}

	public function setMembershipApplicationAuthorizerClass( string $class ): void {
		$this->pimple['fundraising.membership.application.authorizer.class'] = $class;
	}

	private function newCancelMembershipApplicationMailer(): TemplateMailerInterface {
		return $this->newTemplateMailer(
			$this->getOrganizationMessenger(),
			new TwigTemplate(
				$this->getMailerTwig(),
				'Membership_Application_Cancellation_Confirmation.txt.twig',
				[ 'greeting_generator' => $this->getGreetingGenerator() ]
			),
			'mail_subject_confirm_membership_application_cancellation'
		);
	}

	public function newMembershipApplicationConfirmationUseCase( ShowApplicationConfirmationPresenter $presenter, string $accessToken ): ShowApplicationConfirmationUseCase {
		return new ShowApplicationConfirmationUseCase(
			$presenter,
			$this->newMembershipApplicationAuthorizer( null, $accessToken ),
			$this->getMembershipApplicationRepository(),
			$this->newMembershipApplicationTokenFetcher()
		);
	}

	public function newGetDonationUseCase( string $accessToken ): GetDonationUseCase {
		return new GetDonationUseCase(
			$this->newDonationAuthorizer( null, $accessToken ),
			$this->newDonationTokenFetcher(),
			$this->getDonationRepository()
		);
	}

	public function newDonationFormViolationPresenter(): DonationFormViolationPresenter {
		return new DonationFormViolationPresenter( $this->getDonationFormTemplate(), $this->newAmountFormatter() );
	}

	public function newDonationFormPresenter(): DonationFormPresenter {
		return new DonationFormPresenter(
			$this->getDonationFormTemplate(),
			$this->newAmountFormatter(),
			$this->newIsCustomDonationAmountValidator()
		);
	}

	private function getDonationFormTemplate(): TwigTemplate {
		if ($this->getSkinDirectory() === 'skins/10h16/templates')  {
			return $this->getLayoutTemplate(
				'Donation_Form.html.twig',
				[
					'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForDonation(),
					'presetAmounts' => $this->getChoiceFactory()->getAmountOption(),
					'messages' => $this->getMessages()
				]
			);
		}
		return $this->getLayoutTemplate(
			'Donation_Form.html.twig',
			[
				'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForDonation(),
				'presetAmounts' => $this->getPresetAmountsSettings( 'donations' ),
				'messages' => $this->getMessages()
			]
		);
	}

	public function getMembershipApplicationFormTemplate(): TwigTemplate {
		return $this->getLayoutTemplate( 'Membership_Application.html.twig', [
			'presetAmounts' => $this->getPresetAmountsSettings( 'membership' ),
			'paymentTypes' => $this->getPaymentTypesSettings()->getEnabledForMembershipApplication(),
			'messages' => $this->getMessages()
		] );
	}

	public function newHandleSofortPaymentNotificationUseCase( string $updateToken ): SofortPaymentNotificationUseCase {
		return new SofortPaymentNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationConfirmationMailer()
		);
	}

	public function newHandlePayPalPaymentCompletionNotificationUseCase( string $updateToken ): HandlePayPalPaymentCompletionNotificationUseCase {
		return new HandlePayPalPaymentCompletionNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->newDonationConfirmationMailer(),
			$this->newDonationEventLogger()
		);
	}

	public function newMembershipApplicationSubscriptionSignupNotificationUseCase( string $updateToken ): HandleSubscriptionSignupNotificationUseCase {
		return new HandleSubscriptionSignupNotificationUseCase(
			$this->getMembershipApplicationRepository(),
			$this->newMembershipApplicationAuthorizer( $updateToken ),
			$this->newApplyForMembershipMailer(),
			$this->getLogger()
		);
	}

	public function newMembershipApplicationSubscriptionPaymentNotificationUseCase( string $updateToken ): HandleSubscriptionPaymentNotificationUseCase {
		return new HandleSubscriptionPaymentNotificationUseCase(
			$this->getMembershipApplicationRepository(),
			$this->newMembershipApplicationAuthorizer( $updateToken ),
			$this->newApplyForMembershipMailer(),
			$this->getLogger()
		);
	}

	public function getPayPalPaymentNotificationVerifier(): PaymentNotificationVerifier {
		return $this->pimple['paypal-payment-notification-verifier'];
	}

	public function setPayPalPaymentNotificationVerifier( PaymentNotificationVerifier $verifier ): void {
		$this->pimple['paypal-payment-notification-verifier'] = $verifier;
	}

	public function getPayPalMembershipFeeNotificationVerifier(): PaymentNotificationVerifier {
		return $this->pimple['paypal-membership-fee-notification-verifier'];
	}

	public function setPayPalMembershipFeeNotificationVerifier( PaymentNotificationVerifier $verifier ): void {
		$this->pimple['paypal-membership-fee-notification-verifier'] = $verifier;
	}

	public function newCreditCardNotificationUseCase( string $updateToken ): CreditCardNotificationUseCase {
		return new CreditCardNotificationUseCase(
			$this->getDonationRepository(),
			$this->newDonationAuthorizer( $updateToken ),
			$this->getCreditCardService(),
			$this->newDonationConfirmationMailer(),
			$this->getLogger(),
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
			$this->newBankDataConverter()
		);
	}

	public function newMembershipFormViolationPresenter(): MembershipFormViolationPresenter {
		return new MembershipFormViolationPresenter(
			$this->getMembershipApplicationFormTemplate()
		);
	}

	public function setCreditCardService( CreditCardService $ccService ): void {
		$this->pimple['credit-card-api-service'] = $ccService;
	}

	public function getCreditCardService(): CreditCardService {
		return $this->pimple['credit-card-api-service'];
	}

	public function newCreditCardNotificationPresenter(): CreditCardNotificationPresenter {
		return new CreditCardNotificationPresenter(
			$this->config['creditcard']['return-url']
		);
	}

	private function newDoctrineDonationPrePersistSubscriber(): DoctrineDonationPrePersistSubscriber {
		return new DoctrineDonationPrePersistSubscriber(
			$this->getDonationTokenGenerator(),
			$this->getDonationTokenGenerator()
		);
	}

	private function newDoctrineMembershipApplicationPrePersistSubscriber(): DoctrineMembershipApplicationPrePersistSubscriber {
		return new DoctrineMembershipApplicationPrePersistSubscriber(
			$this->getMembershipTokenGenerator(),
			$this->getMembershipTokenGenerator()
		);
	}

	public function setDonationTokenGenerator( TokenGenerator $tokenGenerator ): void {
		$this->pimple['donation_token_generator'] = $tokenGenerator;
	}

	public function setMembershipTokenGenerator( MembershipTokenGenerator $tokenGenerator ): void {
		$this->pimple['fundraising.membership.application.token_generator'] = $tokenGenerator;
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

	private function newDonationPolicyValidator(): AddDonationPolicyValidator {
		return new AddDonationPolicyValidator(
			$this->newDonationAmountPolicyValidator(),
			$this->newTextPolicyValidator( 'fields' ),
			$this->config['email-address-blacklist']
		);
	}

	private function newDonationAmountPolicyValidator(): AmountPolicyValidator {
		// in the future, this might come from the configuration
		return new AmountPolicyValidator( 1000, 1000 );
	}

	public function getDonationTimeframeLimit(): string {
		return $this->config['donation-timeframe-limit'];
	}

	public function newSystemMessageResponse( string $message ): string {
		return $this->getLayoutTemplate( 'System_Message.html.twig' )
			->render( [ 'message' => $message ] );
	}

	public function getMembershipApplicationTimeframeLimit(): string {
		return $this->config['membership-application-timeframe-limit'];
	}

	private function newAddCommentValidator(): AddCommentValidator {
		return new AddCommentValidator();
	}

	private function getPageCache(): Cache {
		return $this->pimple['page_cache'];
	}

	private function getRenderedPageCache(): Cache {
		return $this->pimple['rendered_page_cache'];
	}

	private function getCampaignCache(): CacheProvider {
		return $this->pimple['campaign_cache'];
	}

	public function enableCaching(): void {
		$this->pimple['page_cache'] = function() {
			return new FilesystemCache( $this->getCachePath() . '/pages/raw' );
		};

		$this->pimple['rendered_page_cache'] = function() {
			return new FilesystemCache( $this->getCachePath() . '/pages/rendered' );
		};

		$this->pimple['campaign_cache'] = function() {
			return new FilesystemCache( $this->getCachePath() . '/campaigns' );
		};
	}

	public function setProfiler( Stopwatch $profiler ): void {
		$this->profiler = $profiler;
	}

	public function setEmailValidator( EmailValidator $validator ): void {
		$this->pimple['mail_validator'] = $validator;
	}

	public function setLogger( LoggerInterface $logger ): void {
		$this->pimple['logger'] = $logger;
	}

	public function setPaypalLogger( LoggerInterface $logger ): void {
		$this->pimple['paypal_logger'] = $logger;
	}

	public function setSofortLogger( LoggerInterface $logger ): void {
		$this->pimple['sofort_logger'] = $logger;
	}

	public function getProfilerDataCollector(): ProfilerDataCollector {
		return $this->pimple['profiler_data_collector'];
	}

	private function newIbanValidator(): KontoCheckIbanValidator {
		return new KontoCheckIbanValidator();
	}

	private function newIbanBlockList(): IbanBlocklist {
		return new IbanBlocklist( $this->config['banned-ibans'] );
	}

	public function setFilePrefixer( FilePrefixer $prefixer ): void {
		$this->pimple['cachebusting_fileprefixer'] = $prefixer;
	}

	private function getFilePrefixer(): FilePrefixer {
		return $this->pimple['cachebusting_fileprefixer'];
	}

	private function getFilePrefix(): string {
		$prefixContentFile = $this->getVarPath() . '/file_prefix.txt';
		if ( !file_exists( $prefixContentFile ) ) {
			return '';
		}
		return preg_replace( '/[^0-9a-f]/', '', file_get_contents( $prefixContentFile ) );
	}

	public function newDonationAcceptedEventHandler( string $updateToken ): DonationAcceptedEventHandler {
		return new DonationAcceptedEventHandler(
			$this->newDonationAuthorizer( $updateToken ),
			$this->getDonationRepository(),
			$this->newDonationConfirmationMailer()
		);
	}

	public function newPageNotFoundHtmlPresenter(): PageNotFoundPresenter {
		return new PageNotFoundPresenter( $this->getLayoutTemplate( 'Page_not_found.html.twig' ) );
	}

	public function setPageViewTracker( PageViewTracker $tracker ): void {
		$this->pimple['page_view_tracker'] = function () use ( $tracker )  {
			return $tracker;
		};
	}

	public function getPageViewTracker(): PageViewTracker {
		return $this->pimple['page_view_tracker'];
	}

	public function newServerSideTracker(): ServerSideTracker {
		// the "https:" prefix does NOT get any slashes because baseURL is stored in a protocol-agnostic way
		// (e.g. "//tracking.wikimedia.de" )
		return new PiwikServerSideTracker(
			new \PiwikTracker( $this->config['piwik']['siteId'], 'https:' . $this->config['piwik']['baseUrl'] )
		);
	}

	public function getI18nDirectory(): string {
		return $this->getAbsolutePath( $this->config['i18n-base-path'] ) . '/' . $this->config['locale'];
	}

	/**
	 * If the pathname does not start with a slash, make the path absolute to root dir of application
	 */
	private function getAbsolutePath( string $path ): string {
		if ( $path[0] === '/' ) {
			return $path;
		}
		return __DIR__ . '/../../' . $path;
	}

	public function setContentPagePageSelector( PageSelector $pageSelector ): void {
		$this->pimple['content_page_selector'] = $pageSelector;
	}

	public function getContentPagePageSelector(): PageSelector {
		return $this->pimple['content_page_selector'];
	}

	public function setContentProvider( ContentProvider $contentProvider ): void {
		$this->pimple['content_provider'] = $contentProvider;
	}

	private function getContentProvider(): ContentProvider {
		return $this->pimple['content_provider'];
	}

	public function newMailTemplateFilenameTraversable(): MailTemplateFilenameTraversable {
		return new MailTemplateFilenameTraversable(
			$this->config['mailer-twig']['loaders']['filesystem']['template-dir']
		);
	}

	public function getUrlGenerator(): UrlGenerator {
		return $this->pimple['url_generator'];
	}

	public function setUrlGenerator( UrlGenerator $urlGenerator ): void {
		$this->pimple['url_generator'] = $urlGenerator;
	}

	public function getCookieBuilder(): CookieBuilder {
		return $this->pimple['cookie-builder'];
	}

	public function getPaymentTypesSettings(): PaymentTypesSettings {
		return $this->pimple['payment-types-settings'];
	}

	/**
	 * @return Euro[]
	 */
	public function getPresetAmountsSettings( string $presetType ): array {
		return array_map( function ( int $amount ) {
			return Euro::newFromCents( $amount );
		}, $this->config['preset-amounts'][$presetType] );
	}

	public function newDonationAmountConstraint(): ValidatorConstraint {
		return new RequiredConstraint( [
			new TypeConstraint( [ 'type' => 'digit' ] ),
			new RangeConstraint( [
				'min' => Euro::newFromInt( $this->config['donation-minimum-amount'] )->getEuroCents(),
				'max' => Euro::newFromInt( $this->config['donation-maximum-amount'] )->getEuroCents()
			] )
		] );
	}

	public function newIsCustomDonationAmountValidator(): IsCustomAmountValidator {
		return new IsCustomAmountValidator( $this->getPresetAmountsSettings( 'donations' ) );
	}

	public function getSkinDirectory(): string {
		return $this->getChoiceFactory()->getSkinTemplateDirectory();
	}

	public function getAbsoluteSkinDirectory(): string {
		return $this->getAbsolutePath( $this->getSkinDirectory() );
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
			return new CampaignConfigurationLoader( new Filesystem(), new SimpleFileFetcher(), $this->getCampaignCache() );
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

	public function getBucketSelector(): BucketSelector {
		return $this->createSharedObject( BucketSelector::class, function (): BucketSelector {
			return new BucketSelector( $this->getCampaignCollection(), new RandomBucketSelection() );
		} );
	}

	public function getBucketLogger(): BucketLogger {
		return $this->createSharedObject( 'bucketLogger', function () {
			return new BestEffortBucketLogger(
				new JsonBucketLogger(
					new StreamLogWriter( $this->getSharedResourcesPath() . '/buckets.log' ),
					new SystemClock()
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
}
