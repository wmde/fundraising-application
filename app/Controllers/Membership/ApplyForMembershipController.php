<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplicationValidationResult;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipResponse;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @license GPL-2.0-or-later
 */
class ApplyForMembershipController {

	private FunFunFactory $ffFactory;

	public function index( FunFunFactory $ffFactory, Request $httpRequest, SessionInterface $session ): Response {
		$this->ffFactory = $ffFactory;
		if ( !$ffFactory->getMembershipSubmissionRateLimiter()->isSubmissionAllowed( $session ) ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'membership_application_rejected_limit' ) );
		}

		try {
			$responseModel = $this->callUseCase( $httpRequest );
		}
		catch ( \InvalidArgumentException $ex ) {
			return $this->newFailureResponse( $httpRequest );
		}

		if ( !$responseModel->isSuccessful() ) {
			$this->logValidationErrors( $responseModel->getValidationResult(), $httpRequest );
			return $this->newFailureResponse( $httpRequest );
		}

		return $this->newHttpResponse( $session, $responseModel );
	}

	private function callUseCase( Request $httpRequest ): ApplyForMembershipResponse {
		$applyForMembershipRequest = $this->createMembershipRequest( $httpRequest );

		$this->addFeeToRequestModel( $applyForMembershipRequest, $httpRequest );

		$applyForMembershipRequest->assertNoNullFields()->freeze();

		return $this->ffFactory->newApplyForMembershipUseCase()->applyForMembership( $applyForMembershipRequest );
	}

	private function createMembershipRequest( Request $httpRequest ): ApplyForMembershipRequest {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( $httpRequest->request->get( 'membership_type', '' ) );

		if ( $httpRequest->request->get( 'adresstyp', '' ) === 'firma' ) {
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

		// TODO: Activate this when bounded context is ready
		//       $request->setIncentive( $httpRequest->request->get( 'incentive', '' ) );

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

	private function newFailureResponse( Request $httpRequest ): Response {
		return new Response(
			$this->ffFactory->newMembershipFormViolationPresenter()->present(
				$this->createMembershipRequest( $httpRequest ),
				$httpRequest->request->get( 'showMembershipTypeOption' ) === 'true'
			)
		);
	}

	private function addFeeToRequestModel( ApplyForMembershipRequest $requestModel, Request $httpRequest ) {
		$requestModel->setPaymentAmountInEuros( Euro::newFromCents(
			intval( $httpRequest->request->get( 'membership_fee', '' ) )
		) );
	}

	private function newHttpResponse( SessionInterface $session, ApplyForMembershipResponse $responseModel ): Response {
		$this->ffFactory->getMembershipSubmissionRateLimiter()->setRateLimitCookie( $session );
		$paymentMethodId = $responseModel->getMembershipApplication()->getPayment()->getPaymentMethod()->getId();
		switch ( $paymentMethodId ) {
			case PaymentMethod::DIRECT_DEBIT:
				return $this->newDirectDebitResponse( $responseModel );
			case PaymentMethod::PAYPAL:
				return $this->newPayPalResponse( $responseModel );
			default:
				throw new \LogicException( 'Unknown payment method when generating membership response: ' . $paymentMethodId );
		}
	}

	private function newDirectDebitResponse( ApplyForMembershipResponse $responseModel ): Response {
		return new RedirectResponse(
			$this->ffFactory->getUrlGenerator()->generateAbsoluteUrl(
				'show-membership-confirmation',
				[
					'id' => $responseModel->getMembershipApplication()->getId(),
					'accessToken' => $responseModel->getAccessToken()
				]
			)
		);
	}

	private function newPayPalResponse( ApplyForMembershipResponse $responseModel ): Response {
		return new RedirectResponse(
			$this->ffFactory->newPayPalUrlGeneratorForMembershipApplications()->generateUrl(
				$responseModel->getMembershipApplication()->getId(),
				$responseModel->getMembershipApplication()->getPayment()->getAmount(),
				$responseModel->getMembershipApplication()->getPayment()->getIntervalInMonths(),
				$responseModel->getUpdateToken(),
				$responseModel->getAccessToken()
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
		return trim( preg_replace( [ '/,/', '/\s{2,}/' ], [ ' ', ' ' ], $value ) );
	}

	private function logValidationErrors( ApplicationValidationResult $validationResult, Request $httpRequest ): void {
		$violations = $validationResult->getViolations();
		$formattedConstraintViolations = [];
		foreach ( $violations as $constraintViolationSource => $constraintViolation ) {
			$formattedConstraintViolations['validation_errors'][] = sprintf(
				'Validation for field "%s" failed: "%s"',
				$constraintViolationSource,
				$constraintViolation
			);
		}
		$formattedConstraintViolations['request_data'] = $httpRequest->request->all();
		$this->ffFactory->getValidationErrorLogger()->logViolations(
			'Unexpected server-side form validation errors.',
			array_keys( $violations ),
			$formattedConstraintViolations
		);
	}
}
