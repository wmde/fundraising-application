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
	// status for direct debit
	const STATUS_NEW = 'N';

	// status for bank transfer
	const STATUS_PROMISE = 'Z';

	// statuses for external payments
	const STATUS_EXTERNAL_INCOMPLETE = 'X';
	const STATUS_EXTERNAL_BOOKED = 'B';

	const STATUS_MODERATION = 'P';
	const STATUS_DELETED = 'D';

	/**
	 * @var int|null
	 */
	private $id;

	private $status;

	private $amount;
	private $interval = 0;
	private $paymentType;
	private $bankTransferCode = '';

	private $optsIntoNewsletter;

	/**
	 * @var Donor|null
	 */
	private $donor;

	/**
	 * @var BankData|null
	 */
	private $bankData;

	/**
	 * TODO: move out of Donation
	 * @var TrackingInfo
	 */
	private $trackingInfo;

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	public function setId( int $id = null ) {
		$this->id = $id;
	}

	public function getStatus(): string {
		return $this->status;
	}

	public function setStatus( string $status ) {
		$this->status = $status;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function setAmount( Euro $amount ) {
		$this->amount = $amount;
	}

	public function getInterval(): int {
		return $this->interval;
	}

	public function setInterval( int $interval ) {
		$this->interval = $interval;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ) {
		$this->paymentType = $paymentType;
	}

	public function getBankTransferCode(): string {
		return $this->bankTransferCode;
	}

	public function setBankTransferCode( string $bankTransferCode ) {
		$this->bankTransferCode = $bankTransferCode;
	}

	/**
	 * Returns the Donor or null for anonymous donations.
	 *
	 * @return Donor|null
	 */
	public function getDonor() {
		return $this->donor;
	}

	public function setDonor( Donor $donor = null ) {
		$this->donor = $donor;
	}

	public function getOptsIntoNewsletter(): bool {
		return $this->optsIntoNewsletter;
	}

	public function setOptsIntoNewsletter( bool $optIn ) {
		$this->optsIntoNewsletter = $optIn;
	}

	/**
	 * Returns the BankData for direct debit donations, or null for others.
	 *
	 * @return BankData|null
	 */
	public function getBankData() {
		return $this->bankData;
	}

	public function setBankData( BankData $bankData = null ) {
		$this->bankData = $bankData;
	}

	/**
	 * @throws RuntimeException
	 */
	public function cancel() {
		if ( $this->paymentType !== PaymentType::DIRECT_DEBIT ) {
			throw new RuntimeException( 'Can only cancel direct debit' );
		}

		if ( !$this->statusIsCancellable() ) {
			throw new RuntimeException( 'Can only cancel new donations' );
		}

		$this->status = self::STATUS_DELETED;
	}

	private function statusIsCancellable(): bool {
		return $this->status === self::STATUS_NEW || $this->status === self::STATUS_MODERATION;
	}

	public function getTrackingInfo(): TrackingInfo {
		return $this->trackingInfo;
	}

	public function setTrackingInfo( TrackingInfo $trackingInfo ) {
		$this->trackingInfo = $trackingInfo;
	}

}
