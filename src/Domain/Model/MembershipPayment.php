<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipPayment {

	private $interval;
	private $amount;
	private $bankData;

	public function __construct( int $intervalInMonths, Euro $amount, BankData $bankData ) {
		$this->interval = $intervalInMonths;
		$this->amount = $amount;
		$this->bankData = $bankData;
	}

	public function getIntervalInMonths(): int {
		return $this->interval;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function getBankData(): BankData {
		return $this->bankData;
	}

}
