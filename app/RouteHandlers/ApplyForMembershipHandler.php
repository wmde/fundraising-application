<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplyForMembershipHandler {

	const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
	const SUBMISSION_COOKIE_NAME = 'memapp_timestamp';

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( Request $request ): Response {
		if ( !$this->isSubmissionAllowed( $request ) ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'membership_application_rejected_limit' ) );
		}

		$applyForMembershipRequest = $this->createMembershipRequest( $request );
		$responseModel = $this->ffFactory->newApplyForMembershipUseCase()->applyForMembership( $applyForMembershipRequest );

		if ( $responseModel->isSuccessful() ) {
			$httpResponse = $this->app->redirect(
				$this->app['url_generator']->generate(
					'show-membership-confirmation',
					[
						'id' => $responseModel->getMembershipApplication()->getId(),
						'token' => $responseModel->getAccessToken()
					]
				),
				Response::HTTP_SEE_OTHER
			);
			$cookie = new Cookie( self::SUBMISSION_COOKIE_NAME, date( self::TIMESTAMP_FORMAT ) );
			$httpResponse->headers->setCookie( $cookie );
			return $httpResponse;
		}

		return new Response(
			$this->ffFactory->newMembershipFormViolationPresenter()->present(
				$applyForMembershipRequest,
				$request->request->get( 'showMembershipTypeOption' ) === 'true'
			)
		);
	}

	private function createMembershipRequest( Request $httpRequest ): ApplyForMembershipRequest {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( $httpRequest->request->get( 'membership_type', '' ) );

		if ( $httpRequest->request->get( 'adresstyp', '' )  === 'firma' ) {
			$request->markApplicantAsCompany();
		}

		$request->setApplicantSalutation( $httpRequest->request->get( 'anrede', '' ) );
		$request->setApplicantTitle( $httpRequest->request->get( 'titel', '' ) );
		$request->setApplicantFirstName( $httpRequest->request->get( 'vorname', '' ) );
		$request->setApplicantLastName( $httpRequest->request->get( 'nachname', '' ) );
		$request->setApplicantCompanyName( $httpRequest->request->get( 'firma', '' ) );

		$request->setApplicantStreetAddress( $httpRequest->request->get( 'strasse', '' ) );
		$request->setApplicantPostalCode( $httpRequest->request->get( 'postcode', '' ) );
		$request->setApplicantCity( $httpRequest->request->get( 'ort', '' ) );
		$request->setApplicantCountryCode( $httpRequest->request->get( 'country', '' ) );

		$request->setApplicantEmailAddress( $httpRequest->request->get( 'email', '' ) );
		$request->setApplicantPhoneNumber( $httpRequest->request->get( 'phone', '' ) );
		$request->setApplicantDateOfBirth( $httpRequest->request->get( 'dob', '' ) );

		$request->setPaymentType( $httpRequest->request->get( 'payment_type', '' ) );
		$request->setPaymentIntervalInMonths( (int)$httpRequest->request->get( 'membership_fee_interval', 0 ) );
		// TODO: German format expected here, amount should be converted based on user's locale
		$request->setPaymentAmountInEuros( str_replace( ',', '.', $httpRequest->request->get( 'membership_fee', '' ) ) );

		$request->setTrackingInfo( new MembershipApplicationTrackingInfo(
			$httpRequest->request->get( 'templateCampaign', '' ),
			$httpRequest->request->get( 'templateName', '' )
		) );

		$request->setPiwikTrackingString( $httpRequest->cookies->get( 'spenden_tracking', '' ) );

		$bankData = new BankData();

		$bankData->setBankName( $httpRequest->request->get( 'bank_name', '' ) );
		$bankData->setIban( new Iban( $httpRequest->request->get( 'iban', '' ) ) );
		$bankData->setBic( $httpRequest->request->get( 'bic', '' ) );
		$bankData->setAccount( $httpRequest->request->get( 'account_number', '' ) );
		$bankData->setBankCode( $httpRequest->request->get( 'bank_code', '' ) );

		$bankData->assertNoNullFields()->freeze();
		$request->setBankData( $bankData );
		$request->assertNoNullFields()->freeze();

		return $request;
	}

	private function isSubmissionAllowed( Request $request ) {
		$lastSubmission = $request->cookies->get( self::SUBMISSION_COOKIE_NAME, '' );
		if ( $lastSubmission === '' ) {
			return true;
		}

		$minNextTimestamp = \DateTime::createFromFormat( self::TIMESTAMP_FORMAT, $lastSubmission )
			->add( new \DateInterval( $this->ffFactory->getMembershipApplicationTimeframeLimit() ) );
		if ( $minNextTimestamp > new \DateTime() ) {
			return false;
		}

		return true;
	}

}