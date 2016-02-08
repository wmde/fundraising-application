<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\FunFunFactory $ffFactory
 */

declare(strict_types = 1);

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\DonationRequest;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

$app->get(
	'validate-email',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newValidateEmailUseCase();
		$responseModel = $useCase->validateEmail( $request->get( 'email', '' ) );

		// Presenter code:
		return $app->json( [ 'status' => $responseModel ? 'OK' : 'ERR' ] );
	}
);

$app->get(
	'list-comments.json',
	function( Request $request ) use ( $app, $ffFactory ) {
		$response = $app->json(
			$ffFactory->newCommentListJsonPresenter()->present(
				$ffFactory->newListCommentsUseCase()->listComments(
					new CommentListingRequest( (int)$request->get( 'n' ) )
				)
			)
		);

		if ( $request->get( 'f' ) ) {
			$response->setCallback( $request->get( 'f' ) );
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

// Form for this is provided by route page/SubscriptionForm
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
				return $app->redirect( $app['url_generator']->generate('page', [ 'pageName' => 'SubscriptionModeration' ] ) );
			}
			return $app->redirect( $app['url_generator']->generate('page', [ 'pageName' => 'SubscriptionSuccess' ] ) );
		}
		return $ffFactory->newAddSubscriptionHTMLPresenter()->present( $responseModel, $request->request->all() );
	}
)
->bind( 'subscribe' );

$app->get(
	'check-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$checkIbanResponse = $useCase->checkIban( new Iban( $request->get( 'iban', '' ) ) );
		return $app->json( $ffFactory->newIbanPresenter()->present( $checkIbanResponse ) );
	}
);

$app->get(
	'generate-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$generateIbanRequest = new GenerateIbanRequest(
			$request->get( 'accountNumber', '' ),
			$request->get( 'bankCode', '' )
		);

		$generateIbanResponse = $ffFactory->newGenerateIbanUseCase()->generateIban( $generateIbanRequest );
		return $app->json( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );
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
			$request->get( 'sid', '' ),
			$request->get( 'token', '' ),
			$request->get( 'utoken', '' )
		);

		$ffFactory->newCancelDonationUseCase()->cancelDonation( $cancellationRequest );

		// TODO: response
		return '';
	}
);

$app->post(
	'donation/add',
	function( Application $app, Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newAddDonationUseCase();

		$donationRequest = new DonationRequest();
		$donationRequest->setAmount( floatval( $request->get( 'betrag', 0 ) ) );
		$donationRequest->setPaymentType( $request->get( 'zahlweise', '' ) );
		$donationRequest->setInterval( intval( $request->get( 'periode', 0 ) ) );

		$addressType = $request->get( 'adresstyp', '' );
		$donationRequest->setAddressType( $addressType );
		$donationRequest->setSalutation( $request->get( 'anrede', '' ) );
		$donationRequest->setTitle( $request->get( 'titel', '' ) );
		$donationRequest->setCompanyName( $request->get( 'firma', '' ) );
		$donationRequest->setFirstName( $request->get( 'vorname', '' ) );
		$donationRequest->setLastName( $request->get( 'nachname', '' ) );
		$donationRequest->setPostalAddress( $request->get( 'strasse', '' ) );
		$donationRequest->setPostalCode( $request->get( 'plz', '' ) );
		$donationRequest->setCity( $request->get( 'ort', '' ) );
		$donationRequest->setCountry( $request->get( 'country', '' ) );
		$donationRequest->setEmailAddress( $request->get( 'email', '' ) );

		$donationRequest->setIban( $request->get( 'iban', '' ) );
		$donationRequest->setBic( $request->get( 'bic', '' ) );
		$donationRequest->setBankAccount( $request->get( 'konto', '' ) );
		$donationRequest->setBankCode( $request->get( 'blz', '' ) );
		$donationRequest->setBankName( $request->get( 'bankname', '' ) );

		# TODO: determine tracking data
		$donationRequest->setTracking( '' );
		$donationRequest->setSource( '' );
		$donationRequest->setTotalImpressionCount( $request->get( 'impCount', 0 ) );
		$donationRequest->setSingleBannerImpressionCount( $request->get( 'bImpCount', 0 ) );
		$donationRequest->setColor( $request->get( 'color', '' ) );
		$donationRequest->setSkin( $request->get( 'skin', '' ) );
		$donationRequest->setLayout( $request->get( 'layout', '' ) );

		$donationRequest->setNextForm( $request->get( 'form', '' ) );
		$donationRequest->setCurrentForm( $request->get( 'from_form', '' ) );
		$donationRequest->setLastForm( $request->get( 'back_form', '' ) );

		$responseModel = $useCase->addDonation( $donationRequest, $ffFactory->newAddressValidatorFromAddressType( $addressType ) );
		#if ( in_array( 'application/json', $request->getAcceptableContentTypes() ) ) {
			#return $app->json( $ffFactory->newAddDonationJSONPresenter()->present( $responseModel ) );
		#}
		if ( $responseModel->isSuccessful() ) {
			return $app->redirect( $app['url_generator']->generate('page', [ 'pageName' => 'DonationSuccess' ] ) );
		}
		#return $ffFactory->newAddDonationHTMLPresenter()->present( $responseModel, $request->request->all() );
		return 'TODO';
	}
);

return $app;
