<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Data;

use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke <gabriel.birke@wikimedia.de>
 */
class IncompleteDoctrineDonation {

	public static function newPaypalDonationWithMissingTrackingData(): Donation {
		return ( new self() )->createPaypalDonationWithMissingTrackingData();
	}

	public static function newPaypalDonationWithMissingFields(): Donation {
		return ( new self() )->createPaypalDonationWithMissingFields();
	}

	public static function newDirectDebitDonationWithMissingFields(): Donation {
		return ( new self() )->createDirectDebitDonationWithMissingFields();
	}

	public static function newCreditcardDonationWithMissingFields(): Donation {
		return ( new self() )->createCreditcardDonationWithMissingFields();
	}

	private function createPaypalDonationWithMissingFields() {
		$donation = new Donation();
		$this->setPaymentData( $donation );
		$this->setDonorData( $donation );
		$donation->setPaymentType( PaymentType::PAYPAL );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->encodeAndSetData( array_merge(
			$this->getTrackingInfoArray(),
			$this->getDonorArray(),
			[ 'paypal_payer_id' => ValidPayPalNotificationRequest::PAYER_ID ]
		) );

		return $donation;
	}

	private function createPaypalDonationWithMissingTrackingData() {
		$donation = new Donation();
		$this->setPaymentData( $donation );
		$this->setDonorData( $donation );
		$donation->setPaymentType( PaymentType::PAYPAL );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->encodeAndSetData( array_merge(
			$this->getDonorArray(),
			$this->getPaypalArray()
		) );

		return $donation;
	}

	private function createDirectDebitDonationWithMissingFields() {
		$donation = new Donation();
		$this->setPaymentData( $donation );
		$this->setDonorData( $donation );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->encodeAndSetData( array_merge(
			$this->getTrackingInfoArray(),
			$this->getDonorArray()
		) );

		return $donation;
	}

	private function createCreditcardDonationWithMissingFields() {
		$donation = new Donation();
		$this->setPaymentData( $donation );
		$this->setDonorData( $donation );
		$donation->setPaymentType( PaymentType::CREDIT_CARD );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->encodeAndSetData( array_merge(
			$this->getTrackingInfoArray(),
			$this->getDonorArray()
		) );

		return $donation;
	}

	private function setPaymentData( Donation $donation ) {
		$donation->setAmount( (string)ValidDonation::DONATION_AMOUNT );
		$donation->setPaymentIntervalInMonths( ValidDonation::PAYMENT_INTERVAL_IN_MONTHS );
	}

	private function setDonorData( Donation $donation ) {
		$donation->setDonorCity( ValidDonation::DONOR_CITY );
		$donation->setDonorEmail( ValidDonation::DONOR_EMAIL_ADDRESS );
		$donation->setDonorFullName( ValidDonation::DONOR_FULL_NAME );
		$donation->setDonorOptsIntoNewsletter( ValidDonation::OPTS_INTO_NEWSLETTER );
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

	private function getPaypalArray(): array {
		return [
			'paypal_payer_id' => ValidPayPalNotificationRequest::PAYER_ID,
			'paypal_subscr_id' => ValidPayPalNotificationRequest::SUBSCRIBER_ID,
			'paypal_payer_status' => ValidPayPalNotificationRequest::PAYER_STATUS,
			'paypal_address_status' => ValidPayPalNotificationRequest::PAYER_ADDRESS_STATUS,
			'paypal_mc_gross' => '5.99',
			'paypal_mc_currency' => ValidPayPalNotificationRequest::CURRENCY_CODE,
			'paypal_mc_fee' => '0.18' ,
			'paypal_settle_amount' => '0' ,
			'paypal_first_name' => ValidPayPalNotificationRequest::PAYER_FIRST_NAME,
			'paypal_last_name' => ValidPayPalNotificationRequest::PAYER_LAST_NAME,
			'paypal_address_name' => ValidPayPalNotificationRequest::PAYER_ADDRESS_NAME,
			'ext_payment_id' => ValidPayPalNotificationRequest::TRANSACTION_ID,
			'ext_subscr_id' => ValidPayPalNotificationRequest::SUBSCRIBER_ID,
			'ext_payment_type' => ValidPayPalNotificationRequest::PAYMENT_TYPE,
			'ext_payment_status' => ValidPayPalNotificationRequest::PAYMENT_STATUS_COMPLETED,
			'ext_payment_account' => '',
			'ext_payment_timestamp' => ValidPayPalNotificationRequest::PAYMENT_TIMESTAMP
		];
	}

}
