<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancellationRequest {

	private $applicationId;

	public function __construct( int $applicationId ) {
		$this->applicationId = $applicationId;
	}

	public function getApplicationId(): int {
		return $this->applicationId;
	}

}