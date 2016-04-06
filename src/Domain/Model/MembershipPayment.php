<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipPayment {

	const TYPE_ACTIVE = 'active';
	const TYPE_SUSTAINING = 'sustaining';

	private $type;
	private $interval;
	private $amount;
	private $bankData;

	public function __construct( string $type, int $interval, Euro $amount, BankData $bankData ) {
		$this->type = $type;
		$this->interval = $interval;
		$this->amount = $amount;
		$this->bankData = $bankData;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getInterval(): int {
		return $this->interval;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function getBankData(): BankData {
		return $this->bankData;
	}

}
