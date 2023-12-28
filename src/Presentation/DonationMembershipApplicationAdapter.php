<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\AnonymousDonor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\CompanyDonor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\PersonDonor;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;

class DonationMembershipApplicationAdapter {

	public function __construct(
		private readonly BankDataGenerator $bankDataGenerator
	) {
	}

	public function getInitialMembershipFormValues( Donation $donation, Payment $payment ): array {
		return array_merge(
			$this->getMembershipFormPersonValues( $donation->getDonor() ),
			$this->getMembershipFormBankDataValues( $payment )
		);
	}

	private function getMembershipFormPersonValues( Donor $donor ): array {
		if ( $donor instanceof AnonymousDonor ) {
			return [];
		}

		return array_merge(
			$donor->getName()->toArray(),
			[
				'addressType' => AddressType::donorToPresentationAddressType( $donor ),
				'street' => $donor->getPhysicalAddress()->getStreetAddress(),
				'postcode' => $donor->getPhysicalAddress()->getPostalCode(),
				'city' => $donor->getPhysicalAddress()->getCity(),
				'country' => $donor->getPhysicalAddress()->getCountryCode(),
				'email' => $donor->getEmailAddress(),
			]
		);
	}

	private function getMembershipFormBankDataValues( Payment $payment ): array {
		if ( !$payment instanceof DirectDebitPayment ) {
			return [];
		}

		$displayValues = $payment->getDisplayValues();
		$bankData = $this->bankDataGenerator->getBankDataFromIban( new Iban( $displayValues['iban'] ) );

		return [
			'paymentType' => $displayValues['paymentType'],
			'iban' => $displayValues['iban'],
			'bic' => $displayValues['bic'],
			'bankname' => $bankData->bankName
		];
	}

	public function getInitialValidationState( Donation $donation, Payment $payment ): array {
		$validationState = [];
		if ( $donation->getDonor() instanceof PersonDonor || $donation->getDonor() instanceof CompanyDonor ) {
			$validationState['address'] = true;
		}

		if ( !$payment instanceof DirectDebitPayment ) {
			return $validationState;
		}

		if ( !empty( $payment->getDisplayValues()['iban'] ) ) {
			$validationState['bankData'] = true;
		}

		return $validationState;
	}

}
