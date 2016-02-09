<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Frontend\Domain\Address;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
abstract class PaymentData {

	private $amount = 0.0;
	private $interval = 0;

	public function getAmount(): float {
		return $this->amount;
	}

	public function setAmount( float $amount ) {
		$this->amount = $amount;

		return $this;
	}

	public function getInterval(): int {
		return $this->interval;
	}

	public function setInterval( int $interval ) {
		$this->interval = $interval;

		return $this;
	}

}
