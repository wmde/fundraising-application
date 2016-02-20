<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\Domain\BankData;
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
	private $personalInfo;
	private $optIn;
	private $bankData;
	private $tracking;
	private $source;
	private $totalImpressionCount;
	private $singleBannerImpressionCount;
	private $color;
	private $skin;
	private $layout;

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

	public function getOptIn(): string {
		return $this->optIn;
	}

	public function setOptIn( string $optIn ) {
		$this->assertIsWritable();
		$this->optIn = $optIn;
	}

	public function getBankData(): BankData {
		return $this->bankData;
	}

	public function setBankData( BankData $bankData ) {
		$this->assertIsWritable();
		$this->bankData = $bankData;
	}

	public function getTracking(): string {
		return $this->tracking;
	}

	public function setTracking( string $tracking ) {
		$this->assertIsWritable();
		$this->tracking = $tracking;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->source = $source;
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function setTotalImpressionCount( int $totalImpressionCount ) {
		$this->totalImpressionCount = $totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

	public function setSingleBannerImpressionCount( int $singleBannerImpressionCount ) {
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function setColor( string $color ) {
		$this->color = $color;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function setSkin( string $skin ) {
		$this->skin = $skin;
	}

	public function getLayout(): string {
		return $this->layout;
	}

	public function setLayout( string $layout ) {
		$this->layout = $layout;
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
