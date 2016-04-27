<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\Factories\FunFunFactory $ffFactory
 */

declare( strict_types = 1 );

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use WMDE\Fundraising\Frontend\App\RouteHandlers\AddDonationHandler;
use WMDE\Fundraising\Frontend\App\RouteHandlers\PayPalNotificationHandler;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancellationRequest;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation\ShowDonationConfirmationRequest;

$app->get(
	'validate-email',
	function( Request $request ) use ( $app, $ffFactory ) {
		$validationResult = $ffFactory->getMailValidator()->validate( $request->query->get( 'email', '' ) );
		return $app->json( [ 'status' => $validationResult->isSuccessful() ? 'OK' : 'ERR' ] );
	}
);

$app->post(
	'validate-amount',
	function( Request $request ) use ( $app, $ffFactory ) {

		$amount = (float) $ffFactory->newDecimalNumberFormatter()->parse( $request->get( 'amount', '0' ) );
		$amountValidator = $ffFactory->newAmountValidator();
		$validationResult = $amountValidator->validate( $amount, (string) $request->get( 'paymentType', '' ) );

		if ( $validationResult->isSuccessful() ) {
			return $app->json( [ 'status' => 'OK' ] );
		} else {
			$errors = [];
			foreach( $validationResult->getViolations() as $violation ) {
				$errors[] = $ffFactory->getTranslator()->trans( $violation->getMessageIdentifier() );
			}
			return $app->json( [ 'status' => 'ERR', 'message' => implode( "\n", $errors ) ] );
		}
	}
);

$app->post(
	'validate-address',
	function( Request $request ) use ( $app, $ffFactory ) {
		$routeHandler = new class() {

			public function handle( FunFunFactory $ffFactory, Application $app, Request $request ) {
				if ( $request->get( 'adressType', '' ) === 'anonym' ) {
					return $app->json( [ 'status' => 'OK' ] );
				}

				$personalInfo = $this->getPersonalInfoFromRequest( $request );
				$personalInfoValidator = $ffFactory->newPersonalInfoValidator();
				$validationResult = $personalInfoValidator->validate( $personalInfo );

				if ( $validationResult->isSuccessful() ) {
					return $app->json( [ 'status' => 'OK' ] );
				} else {
					$errors = [];
					foreach( $validationResult->getViolations() as $violation ) {
						$errors[$violation->getSource()] = $ffFactory->getTranslator()->trans( $violation->getMessageIdentifier() );
					}
					return $app->json( [ 'status' => 'ERR', 'messages' => $errors ] );
				}
			}

			private function getPersonalInfoFromRequest( Request $request ): Donor {
				return new Donor(
					$this->getNameFromRequest( $request ),
					$this->getPhysicalAddressFromRequest( $request ),
					$request->get( 'email', '' )
				);
			}

			private function getPhysicalAddressFromRequest( Request $request ): PhysicalAddress {
				$address = new PhysicalAddress();

				$address->setStreetAddress( $request->get( 'street', '' ) );
				$address->setPostalCode( $request->get( 'postcode', '' ) );
				$address->setCity( $request->get( 'city', '' ) );
				$address->setCountryCode( $request->get( 'country', '' ) );

				return $address->freeze()->assertNoNullFields();
			}

			private function getNameFromRequest( Request $request ): PersonName {
				$name = $request->get( 'addressType', '' ) === 'firma'
					? PersonName::newCompanyName() : PersonName::newPrivatePersonName();

				$name->setSalutation( $request->get( 'salutation', '' ) );
				$name->setTitle( $request->get( 'title', '' ) );
				$name->setCompanyName( $request->get( 'company', '' ) );
				$name->setFirstName( $request->get( 'firstName', '' ) );
				$name->setLastName( $request->get( 'lastName', '' ) );

				return $name->freeze()->assertNoNullFields();
			}
		};

		return $routeHandler->handle( $ffFactory, $app, $request );
	}
);

$app->get(
	'list-comments.json',
	function( Request $request ) use ( $app, $ffFactory ) {
		$response = $app->json(
			$ffFactory->newCommentListJsonPresenter()->present(
				$ffFactory->newListCommentsUseCase()->listComments(
					new CommentListingRequest( (int)$request->query->get( 'n' ) )
				)
			)
		);

		if ( $request->query->get( 'f' ) ) {
			$response->setCallback( $request->query->get( 'f' ) );
		}

		return $response;
	}
);

$app->get(
	'list-comments.rss',
	function() use ( $app, $ffFactory ) {
		$rss = $ffFactory->newCommentListRssPresenter()->present(
			$ffFactory->newListCommentsUseCase()->listComments(
				new CommentListingRequest( 100 )
			)
		);

		return new Response(
			$rss,
			200,
			[
				'Content-Type' => 'text/xml; charset=utf-8',
				'X-Moz-Is-Feed' => '1'
			]
		);
	}
);

$app->get(
	'list-comments.html',
	function() use ( $app, $ffFactory ) {
		return new Response(
			$ffFactory->newCommentListHtmlPresenter()->present(
				$ffFactory->newListCommentsUseCase()->listComments(
					new CommentListingRequest( 10 )
				)
			)
		);
	}
);

$app->get(
	'page/{pageName}',
	function( Application $app, $pageName ) use ( $ffFactory ) {
		return $ffFactory->newDisplayPagePresenter()->present(
			$ffFactory->newDisplayPageUseCase()->getPage( new PageDisplayRequest( $pageName ) )
		);
	}
)
->bind( 'page' );

// Form for this is provided by route page/Subscription_Form
$app->post(
	'contact/subscribe',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newAddSubscriptionUseCase();

		$subscriptionRequest = new SubscriptionRequest();
		$subscriptionRequest->setAddress( $request->get( 'address', '' ) );
		$subscriptionRequest->setCity( $request->get( 'city', '' ) );
		$subscriptionRequest->setPostcode( $request->get( 'postcode', '' ) );

		$subscriptionRequest->setFirstName( $request->get( 'firstName', '' ) );
		$subscriptionRequest->setLastName( $request->get( 'lastName', '' ) );
		$subscriptionRequest->setSalutation( $request->get( 'salutation', '' ) );
		$subscriptionRequest->setTitle( $request->get( 'title', '' ) );

		$subscriptionRequest->setEmail( $request->get( 'email', '' ) );

		$subscriptionRequest->setWikiloginFromValues( [
			$request->request->get( 'wikilogin' ),
			$request->cookies->get( 'spenden_wikilogin' ),
		] );

		$responseModel = $useCase->addSubscription( $subscriptionRequest );
		if ( $app['request.is_json'] ) {
			return $app->json( $ffFactory->newAddSubscriptionJSONPresenter()->present( $responseModel ) );
		}
		if ( $responseModel->isSuccessful() ) {
			if ( $responseModel->needsModeration() ) {
				return $app->redirect( $app['url_generator']->generate('page', [ 'pageName' => 'Subscription_Moderation' ] ) );
			}
			return $app->redirect( $app['url_generator']->generate('page', [ 'pageName' => 'Subscription_Success' ] ) );
		}
		return $ffFactory->newAddSubscriptionHTMLPresenter()->present( $responseModel, $request->request->all() );
	}
)
->bind( 'subscribe' );

$app->get( 'contact/confirm-subscription/{confirmationCode}', function ( $confirmationCode ) use ( $ffFactory ) {
	$useCase = $ffFactory->newConfirmSubscriptionUseCase();
	$response = $useCase->confirmSubscription( $confirmationCode );
	return $ffFactory->newConfirmSubscriptionHtmlPresenter()->present( $response );
} )
->assert( 'confirmationCode', '^[0-9a-f]+$' );

$app->get(
	'check-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$checkIbanResponse = $useCase->checkIban( new Iban( $request->query->get( 'iban', '' ) ) );
		return $app->json( $ffFactory->newIbanPresenter()->present( $checkIbanResponse ) );
	}
);

$app->get(
	'generate-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$generateIbanRequest = new GenerateIbanRequest(
			$request->query->get( 'accountNumber', '' ),
			$request->query->get( 'bankCode', '' )
		);

		$generateIbanResponse = $ffFactory->newGenerateIbanUseCase()->generateIban( $generateIbanRequest );
		return $app->json( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );
	}
);

$app->post(
	'add-comment',
	function( Request $request ) use ( $app, $ffFactory ) {
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( $request->request->get( 'kommentar', '' ) );
		$addCommentRequest->setIsPublic( $request->request->get( 'public', '0' ) === '1' );
		$addCommentRequest->setAuthorDisplayName( $request->request->get( 'eintrag', '' ) );
		$addCommentRequest->setDonationId( (int)$request->request->get( 'sid', '' ) );
		$addCommentRequest->freeze()->assertNoNullFields();

		$updateToken = $request->request->get( 'utoken', '' );

		if ( $updateToken === '' ) {
			return $app->json( [
				'status' => 'ERR',
				'message' => 'Required token is missing',
			] );
		}

		$response = $ffFactory->newAddCommentUseCase( $updateToken )->addComment( $addCommentRequest );

		if ( $response->isSuccessful() ) {
			return $app->json( [
				'status' => 'OK',
				'message' => '',
			] );
		}

		return $app->json( [
			'status' => 'ERR',
			'message' => $response->getErrorMessage(),
		] );
	}
);

$app->post(
	'contact/get-in-touch',
	function( Request $request ) use ( $app, $ffFactory ) {
		$contactFormRequest = new GetInTouchRequest(
			$request->get( 'firstname', '' ),
			$request->get( 'lastname', '' ),
			$request->get( 'email', '' ),
			$request->get( 'subject', '' ),
			$request->get( 'messageBody', '' )
		);

		$contactFormResponse = $ffFactory->newGetInTouchUseCase()->processContactRequest( $contactFormRequest );
		if ( $contactFormResponse->isSuccessful() ) {
			return $app->redirect( $app['url_generator']->generate( 'page', [ 'pageName' => 'KontaktBestaetigung' ] ) );
		}
		return $ffFactory->newGetInTouchHTMLPresenter()->present( $contactFormResponse, $request->request->all() );
	}
);

$app->post(
	'donation/cancel',
	function( Request $request ) use ( $app, $ffFactory ) {
		$cancellationRequest = new CancelDonationRequest(
			(int)$request->request->get( 'sid', '' )
		);

		$useCase = $ffFactory->newCancelDonationUseCase( $request->request->get( 'utoken', '' ) );

		return $ffFactory->newCancelDonationHtmlPresenter()->present(
			$useCase->cancelDonation( $cancellationRequest )
		);
	}
);


$app->post(
	'donation/add',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		return ( new AddDonationHandler( $ffFactory, $app ) )->handle( $request );
	}
);

$app->post(
	'apply-for-membership',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newApplyForMembershipUseCase();

		$useCase->applyForMembership( new ApplyForMembershipRequest( /* TODO */ ) );

		return 'TODO'; // TODO
	}
);

$app->post(
	'cancel-membership-application',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		$cancellationRequest = new CancellationRequest(
			(int)$request->request->get( 'sid', '' )
		);

		return $ffFactory->newCancelMembershipApplicationHtmlPresenter()->present(
			$ffFactory->newCancelMembershipApplicationUseCase( $request->request->get( 'utoken', '' ) )
				->cancelApplication( $cancellationRequest )
		);
	}
);

$app->get(
	'show-donation-confirmation',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newShowDonationConfirmationUseCase( $request->query->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			(int)$request->query->get( 'donationId', '' )
		) );

		if ( $responseModel->accessIsPermitted() ) {
			$selectedConfirmationPage = $ffFactory->getDonationConfirmationPageSelector()->selectPage();
			return new Response(
				$ffFactory->newDonationConfirmationPresenter()->present(
					$responseModel->getDonation(),
					$request->query->get( 'updateToken', '' ),
					$selectedConfirmationPage
				)
			);
		}

		return new Response( 'TODO: access not permitted' ); // TODO
	}
);

$app->post(
	'handle-paypal-payment-notification',
	function ( Application $app, Request $request ) use ( $ffFactory ) {
		return ( new PayPalNotificationHandler( $ffFactory ) )->handle( $request );
	}
);

$app->get( '/', function ( Application $app ) {

	// TODO Move code from template to content wiki to have a page name without suffixes (/page/DonationForm)
	$subRequest = Request::create('/page/DonationForm.html.twig', 'GET');

	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
} );

return $app;
