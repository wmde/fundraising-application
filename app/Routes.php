<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\DonationContext\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\App\Controllers\AddCommentController;
use WMDE\Fundraising\Frontend\App\Controllers\AddDonationController;
use WMDE\Fundraising\Frontend\App\Controllers\AddSubscriptionController;
use WMDE\Fundraising\Frontend\App\Controllers\ApplyForMembershipController;
use WMDE\Fundraising\Frontend\App\Controllers\CancelDonationController;
use WMDE\Fundraising\Frontend\App\Controllers\CancelMembershipApplicationController;
use WMDE\Fundraising\Frontend\App\Controllers\ConfirmSubscriptionController;
use WMDE\Fundraising\Frontend\App\Controllers\ContactController;
use WMDE\Fundraising\Frontend\App\Controllers\CreditCardPaymentNotificationController;
use WMDE\Fundraising\Frontend\App\Controllers\DonationAcceptedController;
use WMDE\Fundraising\Frontend\App\Controllers\IbanController;
use WMDE\Fundraising\Frontend\App\Controllers\ListCommentsController;
use WMDE\Fundraising\Frontend\App\Controllers\NewDonationController;
use WMDE\Fundraising\Frontend\App\Controllers\PageDisplayController;
use WMDE\Fundraising\Frontend\App\Controllers\PaypalNotificationController;
use WMDE\Fundraising\Frontend\App\Controllers\PaypalNotificationControllerForMembershipFee;
use WMDE\Fundraising\Frontend\App\Controllers\PurgeCacheController;
use WMDE\Fundraising\Frontend\App\Controllers\ShowDonationConfirmationController;
use WMDE\Fundraising\Frontend\App\Controllers\ShowFaqController;
use WMDE\Fundraising\Frontend\App\Controllers\ShowMembershipConfirmationController;
use WMDE\Fundraising\Frontend\App\Controllers\ShowUpdateAddressController;
use WMDE\Fundraising\Frontend\App\Controllers\ShowUseOfFundsController;
use WMDE\Fundraising\Frontend\App\Controllers\SofortNotificationController;
use WMDE\Fundraising\Frontend\App\Controllers\UpdateAddressController;
use WMDE\Fundraising\Frontend\App\Controllers\UpdateDonorController;
use WMDE\Fundraising\Frontend\App\Controllers\ValidateAddressController;
use WMDE\Fundraising\Frontend\App\Controllers\ValidateDonationAmountController;
use WMDE\Fundraising\Frontend\App\Controllers\ValidateFeeController;
use WMDE\Fundraising\Frontend\App\Controllers\ValidationController;
use WMDE\Fundraising\Frontend\App\RouteHandlers\RouteRedirectionHandler;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\AuthorizedCachePurger;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancellationRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;

class Routes {

	public const ADD_COMMENT_PAGE = 'AddCommentPage';
	public const CANCEL_DONATION = 'cancel-donation';
	public const CANCEL_MEMBERSHIP = 'cancel-membership-application';
	public const CONFIRM_SUBSCRIPTION = 'confirm-subscription';
	public const CONVERT_BANKDATA = 'generate-iban';
	public const GET_IN_TOUCH = 'contact';
	public const INDEX = '/';
	public const LIST_COMMENTS_HTML = 'list-comments.html';
	public const LIST_COMMENTS_RSS = 'list-comments.rss';
	public const POST_COMMENT = 'PostComment';
	public const SHOW_DONATION_CONFIRMATION = 'show-donation-confirmation';
	public const SHOW_DONATION_FORM = 'show-donation-form';
	public const SHOW_FAQ = 'faq';
	public const SHOW_MEMBERSHIP_CONFIRMATION = 'show-membership-confirmation';
	public const SHOW_PAGE = 'page';
	public const SHOW_UPDATE_ADDRESS = 'update-address-show-form';
	public const SHOW_USE_OF_FUNDS = 'use-of-funds';
	public const SUBSCRIBE = 'subscribe';
	public const UPDATE_ADDRESS = 'update-address';
	public const UPDATE_DONOR = 'update-donor';
	public const VALIDATE_ADDRESS = 'validate-donor-address';
	public const VALIDATE_DONATION_AMOUNT = 'validate-donation-amount';
	public const VALIDATE_EMAIL = 'validate-email';
	public const VALIDATE_MEMBERSHIP_FEE = 'validate-fee';
	public const VALIDATE_IBAN = 'check-iban';

	public static function initializeRoutes( Application $app, FunFunFactory $ffFactory ): Application {
		$app->post(
			'validate-email',
			ValidationController::class . '::validateEmail'
		)->bind( self::VALIDATE_EMAIL );

		$app->post(
			// This route is named badly, it validates **donor** information.
			'validate-address',
			ValidateAddressController::class . '::validate'
		)->bind( self::VALIDATE_ADDRESS );

		$app->post(
			'validate-donation-amount',
			ValidateDonationAmountController::class . '::validate'
		)->bind( self::VALIDATE_DONATION_AMOUNT );

		$app->post(
			'validate-fee',
			ValidateFeeController::class . '::validateFee'
		)->bind( self::VALIDATE_MEMBERSHIP_FEE );

		$app->get(
			'list-comments.json',
			ListCommentsController::class . '::handleJson'
		);

		$app->get(
			'list-comments.rss',
			ListCommentsController::class . '::handleRss'
		)->bind( self::LIST_COMMENTS_RSS );

		$app->get(
			'list-comments.html',
			ListCommentsController::class . '::handleHtml'
		)->bind( self::LIST_COMMENTS_HTML );

		$app->get(
			'page/{pageName}',
			PageDisplayController::class . '::handle'
		)->bind( self::SHOW_PAGE );

		$app->match(
			'contact/subscribe',
			AddSubscriptionController::class . '::addSubscription'
		)->method( 'GET|POST' )->bind( self::SUBSCRIBE );

		$app->get(
			'contact/confirm-subscription/{confirmationCode}',
			ConfirmSubscriptionController::class . '::handle'
		)
			->assert( 'confirmationCode', '^[0-9a-f]+$' )
			->bind( self::CONFIRM_SUBSCRIPTION );

		$app->get(
			'check-iban',
			IbanController::class . '::validateIban'
		)->bind( self::VALIDATE_IBAN );

		$app->get(
			'generate-iban',
			IbanController::class . '::convertBankDataToIban'
		)->bind( self::CONVERT_BANKDATA );

		$app->post(
			'add-comment',
			AddCommentController::class . '::addComment'
		)->bind( self::POST_COMMENT );

		$app->get(
			'add-comment',
			AddCommentController::class . '::viewComment'
		)->bind( self::ADD_COMMENT_PAGE );

		$app->post(
			'contact/get-in-touch',
			ContactController::class . '::sendRequest'
		);

		$app->get(
			'contact/get-in-touch',
			ContactController::class . '::viewContactForm'
		)->bind( self::GET_IN_TOUCH );

		$app->get(
			'faq',
			ShowFaqController::class . '::handle'
		)->bind( self::SHOW_FAQ );

		$app->get(
			self::UPDATE_ADDRESS,
			ShowUpdateAddressController::class . '::showForm'
		)->bind( self::SHOW_UPDATE_ADDRESS );

		$app->post(
			self::UPDATE_ADDRESS,
			UpdateAddressController::class . '::updateAddress'

		)->bind( self::UPDATE_ADDRESS );

		$app->get(
			'use-of-funds',
			ShowUseOfFundsController::class . '::handle'
		)->bind( self::SHOW_USE_OF_FUNDS );

		$app->post(
			'donation/cancel',
			CancelDonationController::class . '::handle'
		)->bind( self::CANCEL_DONATION );

		$app->post(
			'donation/add',
			AddDonationController::class . '::handle'
		);

		$app->post(
			'donation/update',
			UpdateDonorController::class . '::updateDonor'
		)->bind( self::UPDATE_DONOR );

		// Show a donation form with pre-filled payment values, e.g. when coming from a banner
		$app->match(
			'donation/new',
			NewDonationController::class . '::handle'
		)->method( 'POST|GET' )
			->bind( self::SHOW_DONATION_FORM );

		$app->post(
			'apply-for-membership',
			ApplyForMembershipController::class . '::applyForMembership'
		);

		$app->get(
			'apply-for-membership',
			ApplyForMembershipController::class . '::showApplicationForm'
		);

		$app->get(
			'show-membership-confirmation',
			ShowMembershipConfirmationController::class . '::handle'
		)->bind( self::SHOW_MEMBERSHIP_CONFIRMATION );

		$app->get(
			'cancel-membership-application',
			CancelMembershipApplicationController::class . '::handle'
		)->bind( self::CANCEL_MEMBERSHIP );

		$app->match(
			'show-donation-confirmation',
			ShowDonationConfirmationController::class . '::show'
		)->bind( self::SHOW_DONATION_CONFIRMATION )
			->method( 'GET|POST' );

		$app->post(
			'handle-paypal-payment-notification',
			PayPalNotificationController::class . '::handle'
		);

		$app->post(
			'sofort-payment-notification',
			SofortNotificationController::class . '::handle'
		);

		$app->get(
			'handle-creditcard-payment-notification',
			CreditCardPaymentNotificationController::class . '::handleNotification'
		);

		$app->get(
			'donation-accepted',
			DonationAcceptedController::class . '::handle'
		);

		$app->post(
			'handle-paypal-membership-fee-payments',
			PayPalNotificationControllerForMembershipFee::class . '::handle'
		);

		$app->get(
			'/',
			function ( Application $app, Request $request ) {
				return $app->handle(
					Request::create(
						'/donation/new',
						'GET',
						$request->query->all(),
						$request->cookies->all(),
						[],
						$request->server->all()
					),
					HttpKernelInterface::SUB_REQUEST
				);
			}
		)->bind( self::INDEX );

		// TODO Figure out how to rewrite with Nginx
		// See https://serverfault.com/questions/805881/nginx-populate-request-uri-with-rewritten-url
		$app->post(
			'/spenden/paypal_handler.php',
			PayPalNotificationController::class . '::handle'
		);

		// redirect display page requests from old URLs
		$app->get(
			'/spenden/{page}',
			function ( Application $app, Request $request, string $page ) {
				// Poor man's rewrite until someone has figured out how to do this with Nginx without breaking REQUEST_URI
				// See https://serverfault.com/questions/805881/nginx-populate-request-uri-with-rewritten-url
				switch ( $page ) {
					case 'Mitgliedschaft':
						return ( new RouteRedirectionHandler( $app, $request->getQueryString() ) )->handle(
							'/page/Membership_Application'
						);
					default:
						return ( new RouteRedirectionHandler( $app, $request->getQueryString() ) )->handle(
							'/page/' . $page
						);
				}
			}
		)->assert( 'page', '[a-zA-Z_\-\s\x7f-\xff]+' );

		// redirect different formats of comment lists
		$app->get(
			'/spenden/{outputFormat}.php',
			function ( Application $app, Request $request, string $outputFormat ) {
				return ( new RouteRedirectionHandler( $app, $request->getQueryString() ) )->handle(
					'/list-comments.' . ( $outputFormat === 'list' ? 'html' : $outputFormat )
				);
			}
		)->assert( 'outputFormat', 'list|rss|json' );

		// redirect all other calls to default route
		$app->get(
			'/spenden{page}',
			function ( Application $app, Request $request ) {
				return ( new RouteRedirectionHandler( $app, $request->getQueryString() ) )->handle( '/' );
			}
		)->assert( 'page', '/?([a-z]+\.php)?' );

		$app->get(
			'/purge-cache',
			PurgeCacheController::class . '::handle'
		);

		$app->get(
			'status',
			function () {
				return 'Status: OK (Online)';
			}
		);

		return $app;
	}

	public static function getNamedRouteUrls( UrlGenerator $urlGenerator ): array {
		return [
			'validateDonationAmount' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_DONATION_AMOUNT ),
			'validateAddress' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_ADDRESS ),
			'validateEmail' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_EMAIL ),
			'validateIban' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_IBAN ),
			'validateMembershipFee' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_MEMBERSHIP_FEE ),
			'convertBankData' => $urlGenerator->generateAbsoluteUrl( self::CONVERT_BANKDATA ),
			'cancelDonation' => $urlGenerator->generateAbsoluteUrl( self::CANCEL_DONATION ),
			'cancelMembership' => $urlGenerator->generateAbsoluteUrl( self::CANCEL_MEMBERSHIP ),
			'postComment' => $urlGenerator->generateAbsoluteUrl( self::POST_COMMENT ),
		];
	}
}
