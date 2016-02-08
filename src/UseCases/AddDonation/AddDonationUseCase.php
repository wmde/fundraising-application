<?php

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\Address;
use WMDE\Fundraising\Frontend\Domain\Address\AnonymousAddress;
use WMDE\Fundraising\Frontend\Domain\Address\CompanyAddress;
use WMDE\Fundraising\Frontend\Domain\Address\PersonAddress;
use WMDE\Fundraising\Frontend\Domain\PaymentData\BankTransferPaymentData;
use WMDE\Fundraising\Frontend\Domain\DonationData;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\PaymentData\DirectDebitPaymentData;
use WMDE\Fundraising\Frontend\Domain\PaymentData\PaymentType;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\AddressValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonAddressValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationUseCase {

	/**
	 * @var DonationRequest
	 */
	private $donationRequest;

	/**
	 * @var DonationRepository
	 */
	private $donationRepository;

	private $addressValidator;

	public function __construct( DonationRepository $donationRepository ) {
		$this->donationRepository = $donationRepository;
	}

	public function addDonation( DonationRequest $donationRequest, AddressValidator $addressValidator ) {
		$this->donationRequest = $donationRequest;
		$this->addressValidator = $addressValidator;

		$donation = $this->createDonationDataFromDonationRequest();
		if ( !$addressValidator->validate( $donation->getAddress() ) ) {
			return ValidationResponse::newFailureResponse( $addressValidator->getConstraintViolations() );
		}

		// TODO: persist donation
		// TODO: send mails

		return ValidationResponse::newSuccessResponse();
	}

	private function createDonationDataFromDonationRequest(): DonationData {
		return new DonationData(
			$this->newAddressFromAddressType( $this->donationRequest->getAddressType() ),
			$this->newPaymentDataFromPaymentType( $this->donationRequest->getPaymentType() )
		);
	}

	private function newAddressFromAddressType( string $addressType ): Address {
		switch( $addressType ) {
			case Address::ADDRESS_TYPE_COMPANY:
				return $this->newCompanyAddress();
			case Address::ADDRESS_TYPE_PERSON:
				return $this->newPersonAddress();
			case Address::ADDRESS_TYPE_ANONYMOUS:
				return $this->newAnonymousAddress();
			default:
				throw new \InvalidArgumentException( 'Address type ' . $addressType . ' not supported' );
		}
	}

	private function newCompanyAddress() {
		return ( new CompanyAddress() )->setCompanyName( $this->donationRequest->getCompanyName() )
			->setAddress( $this->donationRequest->getPostalAddress() )
			->setPostalCode( $this->donationRequest->getPostalCode() )
			->setCity( $this->donationRequest->getCity() )
			->setCountryCode( $this->donationRequest->getCountry() )
			->setEmail( $this->donationRequest->getEmailAddress() );
	}

	private function newPersonAddress() {
		return ( new PersonAddress() )
			->setSalutation( $this->donationRequest->getSalutation() )
			->setTitle( $this->donationRequest->getTitle() )
			->setFirstName( $this->donationRequest->getFirstName() )
			->setLastName( $this->donationRequest->getLastName() )
			->setAddress( $this->donationRequest->getPostalAddress() )
			->setPostalCode( $this->donationRequest->getPostalCode() )
			->setCity( $this->donationRequest->getCity() )
			->setCountryCode( $this->donationRequest->getCountry() )
			->setEmail( $this->donationRequest->getEmailAddress() );
	}

	private function newAnonymousAddress() {
		return new AnonymousAddress();
	}

	private function newPaymentDataFromPaymentType( $paymentType ): PaymentType {
		switch( $paymentType ) {
			case PaymentType::PAYMENT_TYPE_DIRECT_DEBIT:
				return $this->newDirectDebitPaymentData();
			case PaymentType::PAYMENT_TYPE_BANK_TRANSFER:
				return $this->newBankTransferPaymentData();
			/*case PaymentType::PAYMENT_TYPE_PAYPAL:
				return $this->newPayPalPaymentData();
			case PaymentType::PAYMENT_TYPE_CREDIT_CARD:
				return $this->newCreditCardPaymentData();*/
			default:
				throw new \InvalidArgumentException( 'Payment type ' . $paymentType . ' not supported' );
		}
	}

	private function newDirectDebitPaymentData() {
		return ( new DirectDebitPaymentData() )
			->setAmount( $this->donationRequest->getAmount() )
			->setInterval( $this->donationRequest->getInterval() )
			->setIban( $this->donationRequest->getIban() )
			->setBic( $this->donationRequest->getBic() )
			->setAccount( $this->donationRequest->getBankAccount() )
			->setBankCode( $this->donationRequest->getBankCode() )
			->setBankName( $this->donationRequest->getBankName() );
	}

	private function newBankTransferPaymentData() {
		return ( new BankTransferPaymentData() )
			->setAmount( $this->donationRequest->getAmount() )
			->setInterval( $this->donationRequest->getInterval() )
			->setTransferCode();
	}

}