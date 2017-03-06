<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentWithoutAssociatedData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\TransferCodeGenerator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCase {

	private $donationRepository;
	private $donationValidator;
	private $policyValidator;
	private $referrerGeneralizer;
	private $mailer;
	private $transferCodeGenerator;
	private $tokenFetcher;

	public function __construct( DonationRepository $donationRepository, AddDonationValidator $donationValidator,
								 AddDonationPolicyValidator $policyValidator, ReferrerGeneralizer $referrerGeneralizer,
								 DonationConfirmationMailer $mailer, TransferCodeGenerator $transferCodeGenerator,
								 DonationTokenFetcher $tokenFetcher ) {
		$this->donationRepository = $donationRepository;
		$this->donationValidator = $donationValidator;
		$this->policyValidator = $policyValidator;
		$this->referrerGeneralizer = $referrerGeneralizer;
		$this->mailer = $mailer;
		$this->transferCodeGenerator = $transferCodeGenerator;

		$this->tokenFetcher = $tokenFetcher;
	}

	public function addDonation( AddDonationRequest $donationRequest ): AddDonationResponse {
		$validationResult = $this->donationValidator->validate( $donationRequest );

		if ( $validationResult->hasViolations() ) {
			return AddDonationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$donation = $this->newDonationFromRequest( $donationRequest );

		if ( $this->donationNeedsModeration( $donationRequest, $donation ) ) {
			$donation->markForModeration();
		}

		if ( $this->policyValidator->isAutoDeleted( $donationRequest ) ) {
			$donation->markAsDeleted();
		}

		// TODO: handle exceptions
		$this->donationRepository->storeDonation( $donation );

		// TODO: handle exceptions
		$tokens = $this->tokenFetcher->getTokens( $donation->getId() );

		// TODO: handle exceptions
		$this->sendDonationConfirmationEmail( $donation );

		return AddDonationResponse::newSuccessResponse(
			$donation,
			$tokens->getUpdateToken(),
			$tokens->getAccessToken()
		);
	}

	private function newDonationFromRequest( AddDonationRequest $donationRequest ): Donation {
		return new Donation(
			null,
			$this->getInitialDonationStatus( $donationRequest->getPaymentType() ),
			$this->getPersonalInfoFromRequest( $donationRequest ),
			$this->getPaymentFromRequest( $donationRequest ),
			$donationRequest->getOptIn() === '1',
			$this->newTrackingInfoFromRequest( $donationRequest )
		);
	}

	private function getPersonalInfoFromRequest( AddDonationRequest $request ) {
		if ( $request->donorIsAnonymous() ) {
			return null;
		}
		return new Donor(
			$this->getNameFromRequest( $request ),
			$this->getPhysicalAddressFromRequest( $request ),
			$request->getDonorEmailAddress()
		);
	}

	private function getPhysicalAddressFromRequest( AddDonationRequest $request ): DonorAddress {
		$address = new DonorAddress();

		$address->setStreetAddress( $request->getDonorStreetAddress() );
		$address->setPostalCode( $request->getDonorPostalCode() );
		$address->setCity( $request->getDonorCity() );
		$address->setCountryCode( $request->getDonorCountryCode() );

		return $address->freeze()->assertNoNullFields();
	}

	private function getNameFromRequest( AddDonationRequest $request ): DonorName {
		$name = $request->donorIsCompany() ? DonorName::newCompanyName() : DonorName::newPrivatePersonName();

		$name->setSalutation( $request->getDonorSalutation() );
		$name->setTitle( $request->getDonorTitle() );
		$name->setCompanyName( $request->getDonorCompany() );
		$name->setFirstName( $request->getDonorFirstName() );
		$name->setLastName( $request->getDonorLastName() );

		return $name->freeze()->assertNoNullFields();
	}

	private function getInitialDonationStatus( string $paymentType ): string {
		if ( $paymentType === PaymentType::DIRECT_DEBIT ) {
			return Donation::STATUS_NEW;
		}

		if ( $paymentType === PaymentType::BANK_TRANSFER ) {
			return Donation::STATUS_PROMISE;
		}

		return Donation::STATUS_EXTERNAL_INCOMPLETE;
	}

	private function getPaymentFromRequest( AddDonationRequest $donationRequest ): DonationPayment {
		return new DonationPayment(
			$donationRequest->getAmount(),
			$donationRequest->getInterval(),
			$this->getPaymentMethodFromRequest( $donationRequest )
		);
	}

	private function getPaymentMethodFromRequest( AddDonationRequest $donationRequest ): PaymentMethod {
		if ( $donationRequest->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			return new BankTransferPayment( $this->transferCodeGenerator->generateTransferCode() );
		}

		if ( $donationRequest->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			return new DirectDebitPayment( $donationRequest->getBankData() );
		}

		if ( $donationRequest->getPaymentType() === PaymentType::PAYPAL ) {
			return new PayPalPayment( new PayPalData() );
		}

		return new PaymentWithoutAssociatedData( $donationRequest->getPaymentType() );
	}

	private function newTrackingInfoFromRequest( AddDonationRequest $request ): DonationTrackingInfo {
		$trackingInfo = new DonationTrackingInfo();

		$trackingInfo->setTracking( $request->getTracking() );
		$trackingInfo->setSource( $this->referrerGeneralizer->generalize( $request->getSource() ) );
		$trackingInfo->setTotalImpressionCount( $request->getTotalImpressionCount() );
		$trackingInfo->setSingleBannerImpressionCount( $request->getSingleBannerImpressionCount() );
		$trackingInfo->setColor( $request->getColor() );
		$trackingInfo->setSkin( $request->getSkin() );
		$trackingInfo->setLayout( $request->getLayout() );

		return $trackingInfo->freeze()->assertNoNullFields();
	}

	/**
	 * @param Donation $donation
	 *
	 * @throws \RuntimeException
	 */
	private function sendDonationConfirmationEmail( Donation $donation ) {
		if ( $donation->getDonor() !== null && !$donation->hasExternalPayment() ) {
			$this->mailer->sendConfirmationMailFor( $donation );
		}
	}

	private function donationNeedsModeration( AddDonationRequest $donationRequest, Donation $donation ): bool {
		return !$donation->hasExternalPayment() && $this->policyValidator->needsModeration( $donationRequest );
	}

}