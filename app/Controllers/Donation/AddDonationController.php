<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationResponse;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;
use WMDE\Fundraising\PaymentContext\UseCases\CreatePayment\PaymentCreationRequest;
use WMDE\FunValidators\ConstraintViolation;

/**
 * @license GPL-2.0-or-later
 */
class AddDonationController {

	private SessionInterface $session;
	private FunFunFactory $ffFactory;

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$this->session = $session = $request->getSession();
		$this->ffFactory = $ffFactory;
		if ( !$ffFactory->getDonationSubmissionRateLimiter()->isSubmissionAllowed( $session ) ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'donation_rejected_limit' ) );
		}

		$addDonationRequest = $this->createDonationRequest( $request );
		$responseModel = $this->ffFactory->newAddDonationUseCase()->addDonation( $addDonationRequest );

		if ( !$responseModel->isSuccessful() ) {
			$this->logValidationErrors( $responseModel->getValidationErrors() );
			throw new \RuntimeException( "Creating a donation was not successful." );
		}

		return $this->newHttpResponse( $responseModel );
	}

	private function newHttpResponse( AddDonationResponse $responseModel ): Response {
		// for immediately completed payments like Direct Debit / Banktransfer there is no redirect URL
		if ( $responseModel->getPaymentProviderRedirectUrl() === '' ) {
			return new RedirectResponse( $this->ffFactory->getUrlGenerator()->generateAbsoluteUrl(
				Routes::SHOW_DONATION_CONFIRMATION,
				[
					'id' => $responseModel->getDonation()->getId(),
					'accessToken' => $responseModel->getAccessToken()
				]
			) );
		}
		return new RedirectResponse( $responseModel->getPaymentProviderRedirectUrl() );
	}

	private function createDonationRequest( Request $request ): AddDonationRequest {
		$donationRequest = new AddDonationRequest();

		$donationRequest->setDonorType( $this->getSafeDonorType( $request ) );
		$donationRequest->setDonorSalutation( $request->get( 'salutation', '' ) );
		$donationRequest->setDonorTitle( $request->get( 'title', '' ) );
		$donationRequest->setDonorCompany( $request->get( 'companyName', '' ) );
		$donationRequest->setDonorFirstName( $request->get( 'firstName', '' ) );
		$donationRequest->setDonorLastName( $request->get( 'lastName', '' ) );
		$donationRequest->setDonorStreetAddress( $this->filterAutofillCommas( $request->get( 'street', '' ) ) );
		$donationRequest->setDonorPostalCode( $request->get( 'postcode', '' ) );
		$donationRequest->setDonorCity( $request->get( 'city', '' ) );
		$donationRequest->setDonorCountryCode( $request->get( 'country', '' ) );
		$donationRequest->setDonorEmailAddress( $request->get( 'email', '' ) );

		$donationRequest->setTracking( $request->attributes->get( 'trackingCode', '' ) );
		$donationRequest->setOptsIntoNewsletter( (bool)$request->get( 'info', '' ) );
		$donationRequest->setTotalImpressionCount( intval( $request->get( 'impCount', 0 ) ) );
		$donationRequest->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount', 0 ) ) );
		$donationRequest->setOptsIntoDonationReceipt( $request->request->getBoolean( 'donationReceipt', true ) );

		$donationRequest->setPaymentCreationRequest( $this->createPaymentCreationRequest( $request ) );

		return $donationRequest;
	}

	private function createPaymentCreationRequest( Request $request ): PaymentCreationRequest {
		$amount = $this->getEuroAmount( $this->getAmountFromRequest( $request ) );
		$interval = intval( $request->get( 'interval', 0 ) );
		$paymentType = $request->get( 'paymentType', '' );
		$iban = '';
		$bic = '';

		if ( $paymentType === PaymentType::DirectDebit->value ) {
			$iban = ( new Iban( trim( $request->get( 'iban', '' ) ) ) )->toString();
			$bic = trim( $request->get( 'bic', '' ) );
		}

		$paymentCreationRequest = new PaymentCreationRequest( $amount->getEuroCents(), $interval, $paymentType, $iban, $bic );
		$paymentCreationRequest->setDomainSpecificPaymentValidator( $this->ffFactory->newDonationPaymentValidator() );
		return $paymentCreationRequest;
	}

	private function getEuroAmount( int $amount ): Euro {
		try {
			return Euro::newFromCents( $amount );
		} catch ( \InvalidArgumentException $ex ) {
			return Euro::newFromCents( 0 );
		}
	}

	private function getAmountFromRequest( Request $request ): int {
		if ( $request->request->has( 'amount' ) ) {
			return intval( $request->get( 'amount' ) );
		}
		return 0;
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

	/**
	 * @param ConstraintViolation[] $constraintViolations
	 */
	private function logValidationErrors( array $constraintViolations ): void {
		$fields = [];
		$formattedConstraintViolations = [];
		foreach ( $constraintViolations as $constraintViolation ) {
			$source = $constraintViolation->getSource();
			$fields[] = $source;
			$formattedConstraintViolations['validation_errors'][] = sprintf(
				'Validation field "%s" with value "%s" failed with: %s',
				$source,
				$constraintViolation->getValue(),
				$constraintViolation->getMessageIdentifier()
			);
		}

		$this->ffFactory->getValidationErrorLogger()->logViolations(
			'Unexpected server-side form validation errors.',
			$fields,
			$formattedConstraintViolations
		);
	}

	/**
	 * Get AddDonationRequest donor type from HTTP request field.
	 *
	 * Assumes "Anonymous" when field is not set or invalid.
	 *
	 * @param Request $request
	 *
	 * @return DonorType
	 */
	private function getSafeDonorType( Request $request ): DonorType {
		try {
			return DonorType::make(
				AddressType::presentationAddressTypeToDomainAddressType( $request->get( 'addressType', '' ) )
			);
		} catch ( \UnexpectedValueException $e ) {
			return DonorType::ANONYMOUS();
		}
	}
}
