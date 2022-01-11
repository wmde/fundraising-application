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
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;
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
			return new Response(
				$this->ffFactory->newDonationFormViolationPresenter()->present(
					$responseModel->getValidationErrors(),
					$addDonationRequest,
					$this->newTrackingInfoFromRequest( $request )
				)
			);
		}

		return $this->newHttpResponse( $responseModel );
	}

	private function newHttpResponse( AddDonationResponse $responseModel ): Response {
		switch ( $responseModel->getDonation()->getPaymentMethodId() ) {
			case PaymentMethod::DIRECT_DEBIT:
			case PaymentMethod::BANK_TRANSFER:
				return new RedirectResponse(
					$this->ffFactory->getUrlGenerator()->generateAbsoluteUrl(
						Routes::SHOW_DONATION_CONFIRMATION,
						[
							'id' => $responseModel->getDonation()->getId(),
							'accessToken' => $responseModel->getAccessToken()
						]
					)
				);
			case PaymentMethod::PAYPAL:
				return new RedirectResponse(
					$this->ffFactory->newPayPalUrlGeneratorForDonations()->generateUrl(
						$responseModel->getDonation()->getId(),
						$this->generatePayPalInvoiceId( $responseModel->getDonation()->getId() ),
						$responseModel->getDonation()->getAmount(),
						$responseModel->getDonation()->getPaymentIntervalInMonths(),
						$responseModel->getUpdateToken(),
						$responseModel->getAccessToken()
					)
				);
			case PaymentMethod::SOFORT:
				return new RedirectResponse(
					$this->ffFactory->newSofortUrlGeneratorForDonations()->generateUrl(
						$responseModel->getDonation()->getId(),
						$responseModel->getDonation()->getPayment()->getPaymentMethod()->getBankTransferCode(),
						$responseModel->getDonation()->getAmount(),
						$responseModel->getUpdateToken(),
						$responseModel->getAccessToken()
					)
				);
			case PaymentMethod::CREDIT_CARD:
				return new RedirectResponse(
					$this->ffFactory->newCreditCardPaymentUrlGenerator()->buildUrl( $responseModel )
				);
			default:
				throw new \LogicException( 'Unknown Payment method - can\'t determine response' );
		}
	}

	private function createDonationRequest( Request $request ): AddDonationRequest {
		$donationRequest = new AddDonationRequest();

		$donationRequest->setAmount( $this->getEuroAmount( $this->getAmountFromRequest( $request ) ) );
		$donationRequest->setPaymentType( $request->get( 'paymentType', '' ) );
		$donationRequest->setInterval( intval( $request->get( 'interval', 0 ) ) );

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

		if ( $donationRequest->getPaymentType() === PaymentMethod::DIRECT_DEBIT ) {
			$donationRequest->setBankData( $this->getBankDataFromRequest( $request ) );
		}

		$donationRequest->setTracking( $request->attributes->get( 'trackingCode', '' ) );
		$donationRequest->setOptIn( $request->get( 'info', '' ) );
		$donationRequest->setTotalImpressionCount( intval( $request->get( 'impCount', 0 ) ) );
		$donationRequest->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount', 0 ) ) );
		$donationRequest->setOptsIntoDonationReceipt( $request->request->getBoolean( 'donationReceipt', true ) );

		return $donationRequest;
	}

	private function getBankDataFromRequest( Request $request ): BankData {
		$bankData = new BankData();
		$bankData->setIban( new Iban( trim( $request->get( 'iban', '' ) ) ) )
			->setBic( trim( $request->get( 'bic', '' ) ) )
			->setAccount( trim( $request->get( 'konto', '' ) ) )
			->setBankCode( trim( $request->get( 'blz', '' ) ) )
			->setBankName( trim( $request->get( 'bankname', '' ) ) );

		if ( $bankData->isComplete() ) {
			return $bankData->freeze()->assertNoNullFields();
		}

		if ( $bankData->hasIban() ) {
			$bankData = $this->newBankDataFromIban( $bankData->getIban() );
		} elseif ( $bankData->hasCompleteLegacyBankData() ) {
			$bankData = $this->newBankDataFromAccountAndBankCode( $bankData->getAccount(), $bankData->getBankCode() );
		}
		return $bankData->freeze()->assertNoNullFields();
	}

	private function newBankDataFromIban( Iban $iban ): BankData {
		return $this->ffFactory->newBankDataConverter()->getBankDataFromIban( $iban );
	}

	private function newBankDataFromAccountAndBankCode( string $account, string $bankCode ): BankData {
		return $this->ffFactory->newBankDataConverter()->getBankDataFromAccountData( $account, $bankCode );
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

	private function newTrackingInfoFromRequest( Request $request ): ImpressionCounts {
		return new ImpressionCounts(
			intval( $request->get( 'impCount', 0 ) ),
			intval( $request->get( 'bImpCount', 0 ) )
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

	/**
	 * We use the donation primary key as the InvoiceId because they're unique
	 * But we prepend a letter to make sure they don't clash with memberships
	 *
	 * @param int $id
	 * @return string
	 */
	private function generatePayPalInvoiceId( int $id ): string {
		return 'D' . $id;
	}
}
