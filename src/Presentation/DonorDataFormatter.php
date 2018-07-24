<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * Class prepares donor data for presentation
 * @license GNU GPL v2+
 */
class DonorDataFormatter {

	public function getAddressArguments( Donation $donation ): array {
		if ( $donation->getDonor() !== null ) {
			return [
				'salutation' => $donation->getDonor()->getName()->getSalutation(),
				'fullName' => $donation->getDonor()->getName()->getFullName(),
				'firstName' => $donation->getDonor()->getName()->getFirstName(),
				'lastName' => $donation->getDonor()->getName()->getLastName(),
				'streetAddress' => $donation->getDonor()->getPhysicalAddress()->getStreetAddress(),
				'postalCode' => $donation->getDonor()->getPhysicalAddress()->getPostalCode(),
				'city' => $donation->getDonor()->getPhysicalAddress()->getCity(),
				'email' => $donation->getDonor()->getEmailAddress()
			];
		}

		return [
			'isAnonymous' => true
		];
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
	 * Maps the membership application's status to a translatable message key
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
		// TODO use locale to determine the date format
		return ( new \DateTime() )->format( 'd.m.Y' );
	}

	public function getHideBannerCookieDuration(): string {
		return '15552000'; // 180 days
	}
}