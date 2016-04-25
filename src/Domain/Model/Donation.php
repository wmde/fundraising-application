<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

use RuntimeException;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Donation {

	const STATUS_NEW = 'N'; // status for direct debit
	const STATUS_PROMISE = 'Z'; // status for bank transfer
	const STATUS_EXTERNAL_INCOMPLETE = 'X'; // status for external payments
	const STATUS_EXTERNAL_BOOKED = 'B'; // status for external payments
	const STATUS_MODERATION = 'P';
	const STATUS_CANCELLED = 'D';

	const OPTS_INTO_NEWSLETTER = true;
	const DOES_NOT_OPT_INTO_NEWSLETTER = false;

	const NO_APPLICANT = null;

	/**
	 * @var int|null
	 */
	private $id;

	private $status;

	private $optsIntoNewsletter;

	/**
	 * @var Donor|null
	 */
	private $donor;

	/**
	 * @var DonationPayment
	 */
	private $payment;

	/**
	 * TODO: move out of Donation
	 * @var TrackingInfo
	 */
	private $trackingInfo;

	public function __construct( int $id = null, string $status, Donor $donor = null, DonationPayment $payment,
		bool $optsIntoNewsletter, TrackingInfo $trackingInfo ) {

		$this->id = $id;
		$this->status = $status;
		$this->donor = $donor;
		$this->payment = $payment;
		$this->optsIntoNewsletter = $optsIntoNewsletter;
		$this->trackingInfo = $trackingInfo;
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @throws \RuntimeException
	 */
	public function assignId( int $id ) {
		if ( $this->id !== null && $this->id !== $id ) {
			throw new \RuntimeException( 'Id cannot be changed after initial assignment' );
		}

		$this->id = $id;
	}

	public function getStatus(): string {
		return $this->status;
	}

	public function getAmount(): Euro {
		return $this->payment->getAmount();
	}

	public function getPaymentIntervalInMonths(): int {
		return $this->payment->getIntervalInMonths();
	}

	public function getPaymentType(): string {
		return $this->payment->getPaymentMethod()->getType();
	}

	/**
	 * Returns the Donor or null for anonymous donations.
	 *
	 * @return Donor|null
	 */
	public function getDonor() {
		return $this->donor;
	}

	public function getPayment(): DonationPayment {
		return $this->payment;
	}

	public function getPaymentMethod(): PaymentMethod {
		return $this->payment->getPaymentMethod();
	}

	public function getOptsIntoNewsletter(): bool {
		return $this->optsIntoNewsletter;
	}

	/**
	 * @throws RuntimeException
	 */
	public function cancel() {
		if ( $this->getPaymentType() !== PaymentType::DIRECT_DEBIT ) {
			throw new RuntimeException( 'Can only cancel direct debit' );
		}

		if ( !$this->statusIsCancellable() ) {
			throw new RuntimeException( 'Can only cancel new donations' );
		}

		$this->status = self::STATUS_CANCELLED;
	}

	/**
	 * @throws RuntimeException
	 */
	public function confirmBooked() {
		if ( !$this->isPaymentTypeExternal() ) {
			throw new RuntimeException( 'Only external payments can be confirmed as booked' );
		}

		if ( !$this->isStatusIncomplete() ) {
			throw new RuntimeException( 'Only incomplete donations can be confirmed as booked' );
		}

		$this->status = self::STATUS_EXTERNAL_BOOKED;
	}

	public function markForModeration() {
		$this->status = self::STATUS_MODERATION;
	}

	public function getTrackingInfo(): TrackingInfo {
		return $this->trackingInfo;
	}

	/**
	 * @param PayPalData $payPalData
	 * @throws RuntimeException
	 */
	public function addPayPalData( PayPalData $payPalData ) {
		$paymentMethod = $this->payment->getPaymentMethod();

		if ( !( $paymentMethod instanceof PayPalPayment ) ) {
			throw new RuntimeException( 'Cannot set PayPal data on a non PayPal payment' );
		}

		$paymentMethod->addPayPalData( $payPalData );
	}

	private function statusIsCancellable(): bool {
		return $this->status === self::STATUS_NEW || $this->status === self::STATUS_MODERATION;
	}

	private function isPaymentTypeExternal() {
		return in_array( $this->getPaymentType(), [ PaymentType::PAYPAL, PaymentType::CREDIT_CARD ] );
	}

	private function isStatusIncomplete() {
		return $this->status === self::STATUS_EXTERNAL_INCOMPLETE;
	}

}
