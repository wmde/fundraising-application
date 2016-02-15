<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Donation {
	use FreezableValueObject;

	private $amount;
	private $interval = 0;
	private $paymentType;
	private $personalInfo;

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

}
