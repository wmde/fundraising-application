<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\AnonymousDonor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\CompanyDonor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\PersonDonor;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

class DonationMembershipApplicationAdapter {

	public function getInitialMembershipFormValues( Donation $donation ): array {
		return array_merge(
			$this->getMembershipFormPersonValues( $donation->getDonor() ),
			$this->getMembershipFormBankDataValues( $donation->getPaymentMethod() )
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
		] );
	}

	private function getMembershipFormBankDataValues( PaymentMethod $paymentMethod ): array {
		if ( !$paymentMethod instanceof DirectDebitPayment ) {
			return [];
		}

		return [
			'iban' => $paymentMethod->getBankData()->getIban()->toString(),
			'bic' => $paymentMethod->getBankData()->getBic(),
			'bankname' => $paymentMethod->getBankData()->getBankName(),
			// TODO delete the following fields as part of https://phabricator.wikimedia.org/T224220
			'accountNumber' => $paymentMethod->getBankData()->getAccount(),
			'bankCode' => $paymentMethod->getBankData()->getBankCode(),
		];
	}

	public function getInitialValidationState( Donation $donation ): array {
		$validationState = [];
		if ( $donation->getDonor() instanceof PersonDonor || $donation->getDonor() instanceof CompanyDonor ) {
			$validationState['address'] = true;
		}
		if ( $donation->getPaymentMethodId() !== PaymentMethod::DIRECT_DEBIT ) {
			return $validationState;
		}
		/** @var DirectDebitPayment $paymentMethod */
		$paymentMethod = $donation->getPayment()->getPaymentMethod();
		if ( $paymentMethod->getBankData()->hasIban() ) {
			$validationState['bankData'] = true;
		}
		return $validationState;
	}

}
