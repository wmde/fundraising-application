<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Donation {
	use FreezableValueObject;

	// status for direct debit
	const STATUS_NEW = 'N';

	// status for bank transfer
	const STATUS_PROMISE = 'Z';

	// statuses for external payments
	const STATUS_EXTERNAL_INCOMPLETE = 'X';
	const STATUS_EXTERNAL_BOOKED = 'B';

	const STATUS_MODERATION = 'P';
	const STATUS_DELETED = 'D';

	private $status;

	private $amount;
	private $interval = 0;
	private $paymentType;

	private $optsIntoNewsletter;

	/**
	 * @var PersonalInfo|null
	 */
	private $personalInfo;

	/**
	 * TODO: can this not be null as well, for some payment types?
	 *
	 * @var BankData
	 */
	private $bankData;

	/**
	 * @var TrackingInfo
	 */
	private $trackingInfo;

	public function getStatus(): string {
		return $this->status;
	}

	public function setStatus( string $status ) {
		$this->status = $status;
	}

	public function getAmount(): float {
		return $this->amount;
	}

	public function setAmount( float $amount ) {
		$this->assertIsWritable();
		$this->amount = $amount;
	}

	public function getInterval(): int {
		return $this->interval;
	}

	public function setInterval( int $interval ) {
		$this->assertIsWritable();
		$this->interval = $interval;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ) {
		$this->assertIsWritable();
		$this->paymentType = $paymentType;
	}

	/**
	 * Returns the PersonalInfo or null for anonymous donations.
	 *
	 * @return PersonalInfo|null
	 */
	public function getPersonalInfo() {
		return $this->personalInfo;
	}

	public function setPersonalInfo( PersonalInfo $personalInfo = null ) {
		$this->assertIsWritable();
		$this->personalInfo = $personalInfo;
	}

	public function getOptsIntoNewsletter(): bool {
		return $this->optsIntoNewsletter;
	}

	public function setOptsIntoNewsletter( bool $optIn ) {
		$this->assertIsWritable();
		$this->optsIntoNewsletter = $optIn;
	}

	public function getBankData(): BankData {
		return $this->bankData;
	}

	public function setBankData( BankData $bankData ) {
		$this->assertIsWritable();
		$this->bankData = $bankData;
	}

	public function getTrackingInfo(): TrackingInfo {
		return $this->trackingInfo;
	}

	public function setTrackingInfo( TrackingInfo $trackingInfo ) {
		$this->assertIsWritable();
		$this->trackingInfo = $trackingInfo;
	}

	public function getInitialStatus(): string {
		if ( $this->paymentType === PaymentType::DIRECT_DEBIT ) {
			return self::STATUS_NEW;
		}

		if ( $this->paymentType === PaymentType::BANK_TRANSFER ) {
			return self::STATUS_PROMISE;
		}

		return self::STATUS_EXTERNAL_INCOMPLETE;
	}

	public function determineFullName() {
		if ( $this->getPersonalInfo() !== null ) {
			return $this->getPersonalInfo()->getPersonName()->getFullName();
		}

		return 'Anonym';
	}

	public function generateTransferCode() {
		$transferCode = 'W-Q-';

		for ( $i = 0; $i < 6; ++$i ) {
			$transferCode .= $this->getRandomCharacter();
		}
		$transferCode .= '-' . $this->getRandomCharacter();

		return $transferCode;
	}

	private function getRandomCharacter() {
		$charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return $charSet[random_int( 0, strlen( $charSet ) - 1 )];
	}

}
