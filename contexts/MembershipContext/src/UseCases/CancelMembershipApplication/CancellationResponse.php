<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancellationResponse {

	private $applicationId;
	private $isSuccess;

	const IS_SUCCESS = true;
	const IS_FAILURE = false;

	public function __construct( int $applicationId, bool $isSuccess ) {
		$this->applicationId = $applicationId;
		$this->isSuccess = $isSuccess;
	}

	public function getMembershipApplicationId(): int {
		return $this->applicationId;
	}

	public function cancellationWasSuccessful(): bool {
		return $this->isSuccess;
	}

}
