<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use phpDocumentor\Reflection\Types\Scalar;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\MembershipContext\Tracking\MembershipTracking;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplicationValidationResult;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipResponse;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
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

		$applyForMembershipRequest = $this->createMembershipRequest( $httpRequest );

		try {
			$responseModel = $this->callUseCase( $applyForMembershipRequest );
		} catch ( \InvalidArgumentException $ex ) {
			return $this->newFailureResponse( $ffFactory, $httpRequest, $applyForMembershipRequest, [ 'server_error' => 'something went wrong' ] );
		}

		if ( !$responseModel->isSuccessful() ) {
			$validationResult = $responseModel->getValidationResult();
			$this->logValidationErrors( $validationResult, $httpRequest );
			return $this->newFailureResponse( $ffFactory, $httpRequest, $applyForMembershipRequest, $validationResult->getViolations() );
		}

		$this->ffFactory->getMembershipSubmissionRateLimiter()->setRateLimitCookie( $session );
		return new RedirectResponse( $responseModel->getPaymentCompletionUrl() );
	}

	private function callUseCase( ApplyForMembershipRequest $applyForMembershipRequest ): ApplyForMembershipResponse {
		return $this->ffFactory->newApplyForMembershipUseCase()->applyForMembership( $applyForMembershipRequest );
	}

	private function createMembershipRequest( Request $httpRequest ): ApplyForMembershipRequest {
		$membershipTracking = new MembershipTracking(
			$httpRequest->get( 'piwik_campaign', '' ),
			$httpRequest->get( 'piwik_kwd', '' )
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
				trackingInfo: $membershipTracking,
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
				trackingInfo: $membershipTracking,
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

	/**
	 * @param FunFunFactory $ffFactory
	 * @param Request $httpRequest
	 * @param ApplyForMembershipRequest $applyForMembershipRequest
	 * @param array<string, Scalar> $violations
	 *
	 * @return Response
	 */
	private function newFailureResponse( FunFunFactory $ffFactory, Request $httpRequest, ApplyForMembershipRequest $applyForMembershipRequest, array $violations ): Response {
		$urls = Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() );
		$showMembershipTypeOption = $httpRequest->request->get( 'showMembershipTypeOption' ) === 'true';

		$trackingInfo = new ImpressionCounts(
			intval( $httpRequest->get( 'impCount' ) ),
			intval( $httpRequest->get( 'bImpCount' ) )
		);

		return new Response(
			$this->ffFactory->newMembershipApplicationFormPresenter()->present(
				$urls,
				$showMembershipTypeOption,
				$this->getMembershipFormValues( $applyForMembershipRequest ),
				$violations,
				$trackingInfo
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
	 * @param ApplyForMembershipRequest $request
	 *
	 * @return array<string, string>
	 */
	private function getMembershipFormValues( ApplyForMembershipRequest $request ): array {
		$paymentParameters = $request->paymentParameters;
		$bankData = $this->ffFactory->newBankDataConverter()->getBankDataFromIban( new Iban( $paymentParameters->iban ) );

		return [
			'addressType' => $request->isCompanyApplication() ? 'firma' : 'person',
			'salutation' => $request->applicantSalutation,
			'title' => $request->applicantTitle,
			'firstName' => $request->applicantFirstName,
			'lastName' => $request->applicantLastName,
			'companyName' => $request->applicantCompanyName,
			'street' => $request->applicantStreetAddress,
			'postcode' => $request->applicantPostalCode,
			'city' => $request->applicantCity,
			'country' => $request->applicantCountryCode,
			'email' => $request->applicantEmailAddress,
			'iban' => $paymentParameters->iban,
			'bic' => $paymentParameters->bic,
			'accountNumber' => $bankData->account,
			'bankCode' => $bankData->bankCode,
			'bankname' => $bankData->bankName
		];
	}
}
