<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidDoctrineDonation {

	/**
	 * Returns a Doctrine Donation entity equivalent to the domain entity returned
	 * by @see ValidDonation::newDirectDebitDonation
	 *
	 * @return Donation
	 */
	public static function newDirectDebitDoctrineDonation(): Donation {
		return ( new self() )->createDonation();
	}

	private function createDonation(): Donation {
		$donation = new Donation();

		$donation->setStatus( Donation::STATUS_NEW );

		$donation->setAmount( (string)ValidDonation::DONATION_AMOUNT );
		$donation->setPaymentIntervalInMonths( ValidDonation::PAYMENT_INTERVAL_IN_MONTHS );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$donation->setDonorCity( ValidDonation::DONOR_CITY );
		$donation->setDonorEmail( ValidDonation::DONOR_EMAIL_ADDRESS );
		$donation->setDonorFullName( ValidDonation::DONOR_FULL_NAME );
		$donation->setDonorOptsIntoNewsletter( ValidDonation::OPTS_INTO_NEWSLETTER );

		$donation->encodeAndSetData( array_merge(
			$this->getTrackingInfoArray(),
			$this->getBankDataArray(),
			$this->getDonorArray()
		) );

		return $donation;
	}

	private function getTrackingInfoArray(): array {
		return [
			'layout' => ValidDonation::TRACKING_LAYOUT,
			'impCount' => ValidDonation::TRACKING_TOTAL_IMPRESSION_COUNT,
			'bImpCount' => ValidDonation::TRACKING_BANNER_IMPRESSION_COUNT,
			'tracking' => ValidDonation::TRACKING_TRACKING,
			'skin' => ValidDonation::TRACKING_SKIN,
			'color' => ValidDonation::TRACKING_COLOR,
			'source' => ValidDonation::TRACKING_SOURCE,
		];
	}

	private function getBankDataArray(): array {
		return [
			'iban' => ValidDonation::PAYMENT_IBAN,
			'bic' => ValidDonation::PAYMENT_BIC,
			'konto' => ValidDonation::PAYMENT_BANK_ACCOUNT,
			'blz' => ValidDonation::PAYMENT_BANK_CODE,
			'bankname' => ValidDonation::PAYMENT_BANK_NAME,
		];
	}

	private function getDonorArray(): array {
		return array_merge(
			$this->getPersonNameArray(),
			$this->getAddressArray(),
			[ 'email' => ValidDonation::DONOR_EMAIL_ADDRESS ]
		);
	}

	private function getPersonNameArray(): array {
		return [
			'adresstyp' => 'person',
			'anrede' => ValidDonation::DONOR_SALUTATION,
			'titel' => ValidDonation::DONOR_TITLE,
			'vorname' => ValidDonation::DONOR_FIRST_NAME,
			'nachname' => ValidDonation::DONOR_LAST_NAME,
			'firma' => '',
		];
	}

	private function getAddressArray(): array {
		return [
			'strasse' => ValidDonation::DONOR_STREET_ADDRESS,
			'plz' => ValidDonation::DONOR_POSTAL_CODE,
			'ort' => ValidDonation::DONOR_CITY,
			'country' => ValidDonation::DONOR_COUNTRY_CODE,
		];
	}

}
