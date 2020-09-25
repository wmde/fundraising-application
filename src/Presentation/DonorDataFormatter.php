<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\AnonymousDonor;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * Class prepares donor data for presentation
 * @license GPL-2.0-or-later
 */
class DonorDataFormatter {

	public function getAddressArguments( Donation $donation ): array {
		if ( $donation->getDonor() instanceof AnonymousDonor ) {
			return [
				'isAnonymous' => true
			];
		}
		return array_merge(
			$donation->getDonor()->getName()->toArray(),
			[
				'fullName' => $donation->getDonor()->getName()->getFullName(),
				'streetAddress' => $donation->getDonor()->getPhysicalAddress()->getStreetAddress(),
				'postalCode' => $donation->getDonor()->getPhysicalAddress()->getPostalCode(),
				'city' => $donation->getDonor()->getPhysicalAddress()->getCity(),
				'countryCode' => $donation->getDonor()->getPhysicalAddress()->getCountryCode(),
				'email' => $donation->getDonor()->getEmailAddress()
			] );
	}

	public function getBankTransferCode( PaymentMethod $paymentMethod ): string {
		if ( $paymentMethod instanceof BankTransferPayment ) {
			return $paymentMethod->getBankTransferCode();
		}

		return '';
	}

	public function getBankDataArguments( PaymentMethod $paymentMethod ): array {
		if ( $paymentMethod instanceof DirectDebitPayment ) {
			return [
				'iban' => $paymentMethod->getBankData()->getIban()->toString(),
				'bic' => $paymentMethod->getBankData()->getBic(),
				'bankname' => $paymentMethod->getBankData()->getBankName(),
			];
		}

		return [];
	}

	/**
	 * Map donation status to a translatable message key
	 *
	 * @param string $status
	 * @return string
	 */
	public function mapStatus( string $status ): string {
		switch ( $status ) {
			case Donation::STATUS_MODERATION:
				return 'status-pending';
			case Donation::STATUS_NEW:
				return 'status-new';
			case Donation::STATUS_EXTERNAL_INCOMPLETE:
				return 'status-unconfirmed';
			case Donation::STATUS_PROMISE:
				return 'status-pledge';
			case Donation::STATUS_EXTERNAL_BOOKED:
				return 'status-booked';
			case Donation::STATUS_CANCELLED:
				return 'status-canceled';
			default:
				return 'status-unknown';
		}
	}

	public function getDonationDate(): string {
		return ( new \DateTime() )->format( 'd.m.Y' );
	}

	public function getHideBannerCookieDuration(): string {
		// 180 days
		return '15552000';
	}
}
