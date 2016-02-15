<?php

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\Address;
use WMDE\Fundraising\Frontend\Domain\Address\AnonymousAddress;
use WMDE\Fundraising\Frontend\Domain\Address\CompanyAddress;
use WMDE\Fundraising\Frontend\Domain\Address\PersonAddress;
use WMDE\Fundraising\Frontend\Domain\Donation;
use WMDE\Fundraising\Frontend\Domain\PaymentData\BankTransferPaymentData;
use WMDE\Fundraising\Frontend\Domain\DonationData;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\PaymentData\DirectDebitPaymentData;
use WMDE\Fundraising\Frontend\Domain\PaymentData\PaymentType;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\AddressValidator;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonAddressValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCase {

	private $donationRepository;
	private $donationValidator;

	public function __construct( DonationRepository $donationRepository, DonationValidator $donationValidator ) {
		$this->donationRepository = $donationRepository;
		$this->donationValidator = $donationValidator;
	}

	public function addDonation( AddDonationRequest $donationRequest ) {
		$donation = new Donation();

		$donation->setAmount( $donationRequest->getAmount() );
		$donation->setPersonalInfo( $donationRequest->getPersonalInfo() );

		if ( $donationRequest->getPaymentType() !== 'BEZ' ) {
			// TODO: this should not be done via an exception
			throw new \RuntimeException( 'Payment type CASH not supported' );
		}

		$validationResult = $this->donationValidator->validate( $donation );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		// TODO: persist donation
		// TODO: send mails

		return ValidationResponse::newSuccessResponse();
	}



}