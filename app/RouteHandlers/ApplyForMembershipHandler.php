<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\MembershipApplicationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipResponse;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplyForMembershipHandler {

	public const SUBMISSION_COOKIE_NAME = 'memapp_timestamp';
	public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( Request $httpRequest ): Response {
		if ( !$this->isSubmissionAllowed( $httpRequest ) ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'membership_application_rejected_limit' ) );
		}

		try {
			$responseModel = $this->callUseCase( $httpRequest );
		}
		catch ( \InvalidArgumentException $ex ) {
			return $this->newFailureResponse( $httpRequest );
		}

		if ( !$responseModel->isSuccessful() ) {
			return $this->newFailureResponse( $httpRequest );
		}

		$this->logSelectedBuckets( $responseModel );

		return $this->newHttpResponse( $responseModel );
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function callUseCase( Request $httpRequest ) {
		$applyForMembershipRequest = $this->createMembershipRequest( $httpRequest );

		$this->addFeeToRequestModel( $applyForMembershipRequest, $httpRequest );

		$applyForMembershipRequest->assertNoNullFields()->freeze();

		return $this->ffFactory->newApplyForMembershipUseCase()->applyForMembership( $applyForMembershipRequest );
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

		$request->setApplicantStreetAddress( $this->filterAutofillCommas( $httpRequest->request->get( 'strasse', '' ) ) );
		$request->setApplicantPostalCode( $httpRequest->request->get( 'postcode', '' ) );
		$request->setApplicantCity( $httpRequest->request->get( 'ort', '' ) );
		$request->setApplicantCountryCode( $httpRequest->request->get( 'country', '' ) );

		$request->setApplicantEmailAddress( $httpRequest->request->get( 'email', '' ) );
		$request->setApplicantPhoneNumber( $httpRequest->request->get( 'phone', '' ) );
		$request->setApplicantDateOfBirth( $httpRequest->request->get( 'dob', '' ) );

		$request->setPaymentType( $httpRequest->request->get( 'payment_type', '' ) );
		$request->setPaymentIntervalInMonths( (int)$httpRequest->request->get( 'membership_fee_interval', 0 ) );

		$request->setTrackingInfo( new MembershipApplicationTrackingInfo(
			$httpRequest->request->get( 'templateCampaign', '' ),
			$httpRequest->request->get( 'templateName', '' )
		) );

		$request->setPiwikTrackingString( $httpRequest->attributes->get( 'trackingCode' ) );

		$request->setOptsIntoDonationReceipt( $httpRequest->request->getBoolean( 'donationReceipt', true ) );

		$request->setBankData( $this->createBakData( $httpRequest ) );

		return $request;
	}

	private function createBakData( Request $httpRequest ): BankData {
		$bankData = new BankData();

		$bankData->setBankName( $httpRequest->request->get( 'bank_name', '' ) );
		$bankData->setIban( new Iban( $httpRequest->request->get( 'iban', '' ) ) );
		$bankData->setBic( $httpRequest->request->get( 'bic', '' ) );
		$bankData->setAccount( $httpRequest->request->get( 'account_number', '' ) );
		$bankData->setBankCode( $httpRequest->request->get( 'bank_code', '' ) );

		$bankData->assertNoNullFields()->freeze();

		return $bankData;
	}

	private function newFailureResponse( Request $httpRequest ) {
		return new Response(
			$this->ffFactory->newMembershipFormViolationPresenter()->present(
				$this->createMembershipRequest( $httpRequest ),
				$httpRequest->request->get( 'showMembershipTypeOption' ) === 'true'
			)
		);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function addFeeToRequestModel( ApplyForMembershipRequest $requestModel, Request $httpRequest ) {
		// TODO: German format expected here, amount should be converted based on user's locale
		$requestModel->setPaymentAmountInEuros( Euro::newFromString(
			str_replace(
				',',
				'.',
				$httpRequest->request->get( 'membership_fee', '' )
			)
		) );
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

	private function newHttpResponse( ApplyForMembershipResponse $responseModel ): Response {
		switch( $responseModel->getMembershipApplication()->getPayment()->getPaymentMethod()->getId() ) {
			case PaymentMethod::DIRECT_DEBIT:
				$httpResponse = $this->newDirectDebitResponse( $responseModel );
				break;
			case PaymentMethod::PAYPAL:
				$httpResponse = $this->newPayPalResponse( $responseModel );
				break;
			default:
				throw new \LogicException( 'This code should not be reached' );
		}

		$this->addCookie( $httpResponse );

		return $httpResponse;
	}

	private function newDirectDebitResponse( ApplyForMembershipResponse $responseModel ): Response {
		return $this->app->redirect(
			$this->app['url_generator']->generate(
				'show-membership-confirmation',
				[
					'id' => $responseModel->getMembershipApplication()->getId(),
					'accessToken' => $responseModel->getAccessToken()
				]
			),
			Response::HTTP_SEE_OTHER
		);
	}

	private function newPayPalResponse( ApplyForMembershipResponse $responseModel ): Response {
		return $this->app->redirect(
			$this->ffFactory->newPayPalUrlGeneratorForMembershipApplications()->generateUrl(
				$responseModel->getMembershipApplication()->getId(),
				$responseModel->getMembershipApplication()->getPayment()->getAmount(),
				$responseModel->getMembershipApplication()->getPayment()->getIntervalInMonths(),
				$responseModel->getUpdateToken(),
				$responseModel->getAccessToken()
			)
		);
	}

	private function addCookie( Response $httpResponse ) {
		$httpResponse->headers->setCookie(
			$this->ffFactory->getCookieBuilder()->newCookie(
				self::SUBMISSION_COOKIE_NAME,
				date( self::TIMESTAMP_FORMAT )
			)
		);

	}

	/**
	 * Safari and Chrome concatenate street autofill values (e.g. house number and street name) with a comma.
	 * This method removes the commas.
	 *
	 * @param string $value
	 * @return string
	 */
	private function filterAutofillCommas( string $value ): string {
		return trim( preg_replace( ['/,/', '/\s{2,}/'], [' ', ' '], $value ) );
	}

	private function logSelectedBuckets( ApplyForMembershipResponse $responseModel ) {
		$this->ffFactory->getBucketLogger()->writeEvent(
			new MembershipApplicationCreated( $responseModel->getMembershipApplication()->getId() ),
			...$this->ffFactory->getSelectedBuckets()
		);
	}
}