<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipResponse {

	public static function newSuccessResponse(): self {
		return new self( true );
	}

	public static function newFailureResponse(): self {
		return new self( false );
	}

	private $isSuccess;

	private function __construct( bool $isSuccess ) {
		$this->isSuccess = $isSuccess;
	}

	public function isSuccessful(): bool {
		return $this->isSuccess;
	}

}
