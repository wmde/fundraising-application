<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PhoneNumber {

	private $phoneNumber;

	public function __construct( string $phoneNumber ) {
		$this->phoneNumber = $phoneNumber;
	}

	public function __toString(): string {
		return $this->phoneNumber;
	}

}
