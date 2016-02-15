<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * Encapsulates a list of payment types.
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PaymentTypes {

	private $paymentTypes;

	/**
	 * @param string[] $paymentTypes e. g. [ 'type' => 'display name' ]
	 */
	public function __construct( array $paymentTypes ) {
		$this->paymentTypes = $paymentTypes;
	}

	/**
	 * Get list of payment types
	 *
	 * @return string[]
	 */
	public function getList(): array {
		return $this->paymentTypes;
	}

	/**
	 * @return string[]
	 */
	public function getKeys(): array {
		return array_keys( $this->paymentTypes );
	}

}
