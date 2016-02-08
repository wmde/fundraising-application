<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\PaymentData\PaymentType;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationData {

	private $address;
	private $paymentData;
	# TODO: $trackingData

	public function __construct( Address $address, PaymentType $paymentType ) {
		$this->address = $address;
		$this->paymentData = $paymentType;
	}

	public function getAddress(): Address {
		return $this->address;
	}

	public function setAddress( Address $address ) {
		$this->address = $address;
	}

	public function getPaymentData(): PaymentData {
		return $this->paymentData;
	}

	public function setPaymentData( PaymentData $paymentData ) {
		$this->paymentData = $paymentData;
	}

}
