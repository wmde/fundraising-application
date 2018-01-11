<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;

class DonationMembershipApplicationAdapter {

	public function getInitialMembershipFormValues( Donation $donation ): array {
		return array_merge(
			$this->getMembershipFormPersonValues( $donation->getDonor() ),
			$this->getMembershipFormBankDataValues( $donation->getPaymentMethod() )
		);
	}

	private function getMembershipFormPersonValues( ? Donor $donor ): array {
		if ( $donor === null ) {
			return [];
		}

		return [
			'addressType' => $donor->getName()->getPersonType(),
			'salutation' => $donor->getName()->isPrivatePerson() ? $donor->getName()->getSalutation() : '',
			'title' => $donor->getName()->getTitle(),
			'firstName' => $donor->getName()->getFirstName(),
			'lastName' => $donor->getName()->getLastName(),
			'companyName' => $donor->getName()->getCompanyName(),
			'street' => $donor->getPhysicalAddress()->getStreetAddress(),
			'postcode' => $donor->getPhysicalAddress()->getPostalCode(),
			'city' => $donor->getPhysicalAddress()->getCity(),
			'country' => $donor->getPhysicalAddress()->getCountryCode(),
			'email' => $donor->getEmailAddress(),
		];
	}

	private function getMembershipFormBankDataValues( PaymentMethod $paymentMethod ): array {
		if ( !$paymentMethod instanceof DirectDebitPayment ) {
			return [];
		}

		return [
			'iban' => $paymentMethod->getBankData()->getIban()->toString(),
			'bic' => $paymentMethod->getBankData()->getBic(),
			'accountNumber' => $paymentMethod->getBankData()->getAccount(),
			'bankCode' => $paymentMethod->getBankData()->getBankCode(),
			'bankname' => $paymentMethod->getBankData()->getBankName(),
		];
	}

	public function getInitialValidationState( Donation $donation ): array {
		$validationState = [];
		if ( $donation->getDonor() !== null ) {
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
