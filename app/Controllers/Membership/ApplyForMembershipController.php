<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplicationValidationResult;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipResponse;
use WMDE\Fundraising\PaymentContext\UseCases\CreatePayment\PaymentParameters;

class ApplyForMembershipController {

	private const TRANSFER_CODE_PREFIX = 'XM';

	private FunFunFactory $ffFactory;

	public function index( FunFunFactory $ffFactory, Request $httpRequest ): Response {
		$session = $httpRequest->getSession();
		$this->ffFactory = $ffFactory;
		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );
		if ( !$ffFactory->getMembershipSubmissionRateLimiter()->isSubmissionAllowed( $session ) ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'membership_application_rejected_limit' ) );
		}

		try {
			$responseModel = $this->callUseCase( $httpRequest );
		} catch ( \InvalidArgumentException $ex ) {
			return $this->newFailureResponse( $httpRequest );
		}

		if ( !$responseModel->isSuccessful() ) {
			$this->logValidationErrors( $responseModel->getValidationResult(), $httpRequest );
			return $this->newFailureResponse( $httpRequest );
		}

		$this->ffFactory->getMembershipSubmissionRateLimiter()->setRateLimitCookie( $session );
		$this->recordBannerImpressions( $httpRequest, $ffFactory );
		return new RedirectResponse( $responseModel->getPaymentCompletionUrl() );
	}

	private function callUseCase( Request $httpRequest ): ApplyForMembershipResponse {
		$applyForMembershipRequest = $this->createMembershipRequest( $httpRequest );

		return $this->ffFactory->newApplyForMembershipUseCase()->applyForMembership( $applyForMembershipRequest );
	}

	private function createMembershipRequest( Request $httpRequest ): ApplyForMembershipRequest {
		$trackingInfo = new MembershipApplicationTrackingInfo(
			$httpRequest->request->get( 'templateCampaign', '' ),
			$httpRequest->request->get( 'templateName', '' )
		);

		if ( $httpRequest->request->get( 'adresstyp', '' ) === 'firma' ) {
			return ApplyForMembershipRequest::newCompanyApplyForMembershipRequest(
				membershipType: $httpRequest->request->get( 'membership_type', '' ),
				applicantCompanyName: $httpRequest->request->get( 'firma', '' ),
				applicantStreetAddress: $this->filterAutofillCommas( $httpRequest->request->get( 'strasse', '' ) ),
				applicantPostalCode: $httpRequest->request->get( 'postcode', '' ),
				applicantCity: $httpRequest->request->get( 'ort', '' ),
				applicantCountryCode: $httpRequest->request->get( 'country', '' ),
				applicantEmailAddress: $httpRequest->request->get( 'email', '' ),
				optsIntoDonationReceipt: $httpRequest->request->getBoolean( 'donationReceipt', true ),
				incentives: array_filter( $httpRequest->request->all( 'incentives' ) ),
				paymentParameters: $this->newPaymentParameters( $httpRequest ),
				trackingInfo: $trackingInfo,
			);
		} else {
			return ApplyForMembershipRequest::newPrivateApplyForMembershipRequest(
				membershipType: $httpRequest->request->get( 'membership_type', '' ),
				applicantSalutation: $httpRequest->request->get( 'anrede', '' ),
				applicantTitle: $httpRequest->request->get( 'titel', '' ),
				applicantFirstName: $httpRequest->request->get( 'vorname', '' ),
				applicantLastName: $httpRequest->request->get( 'nachname', '' ),
				applicantStreetAddress: $this->filterAutofillCommas( $httpRequest->request->get( 'strasse', '' ) ),
				applicantPostalCode: $httpRequest->request->get( 'postcode', '' ),
				applicantCity: $httpRequest->request->get( 'ort', '' ),
				applicantCountryCode: $httpRequest->request->get( 'country', '' ),
				applicantEmailAddress: $httpRequest->request->get( 'email', '' ),
				optsIntoDonationReceipt: $httpRequest->request->getBoolean( 'donationReceipt', true ),
				incentives: array_filter( $httpRequest->request->all( 'incentives' ) ),
				paymentParameters: $this->newPaymentParameters( $httpRequest ),
				trackingInfo: $trackingInfo,
				applicantDateOfBirth: $httpRequest->request->get( 'dob', '' ),
			);
		}
	}

	private function newPaymentParameters( Request $httpRequest ): PaymentParameters {
		return new PaymentParameters(
			$httpRequest->request->getInt( 'membership_fee', 0 ),
			$httpRequest->request->getInt( 'membership_fee_interval', 0 ),
			$httpRequest->request->get( 'payment_type', '' ),
			trim( $httpRequest->request->get( 'iban', '' ) ),
			trim( $httpRequest->request->get( 'bic', '' ) ),
			self::TRANSFER_CODE_PREFIX
		);
	}

	private function newFailureResponse( Request $httpRequest ): Response {
		return new Response(
			$this->ffFactory->newMembershipFormViolationPresenter()->present(
				$this->createMembershipRequest( $httpRequest ),
				$httpRequest->request->get( 'showMembershipTypeOption' ) === 'true'
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

	/**
	 * @deprecated Remove after 2023/2024 thank you campaign
	 */
	private function recordBannerImpressions( Request $httpRequest, FunFunFactory $ffFactory ): void {
		$tracking = $httpRequest->attributes->get( 'trackingCode', '' );
		if ( $tracking === '' ) {
			return;
		}
		$overallImpressionCount = intval( $httpRequest->get( 'impCount' ) );
		$bannerImpressionCount = intval( $httpRequest->get( 'bImpCount' ) );
		$ffFactory->getMembershipImpressionCounter()->countImpressions( $bannerImpressionCount, $overallImpressionCount, $tracking );
	}
}
