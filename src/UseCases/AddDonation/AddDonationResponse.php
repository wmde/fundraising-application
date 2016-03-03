<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationResponse {

	private $validationErrors;
	private $donation;

	/**
	 * @param Donation $donation
	 * @param ConstraintViolation[] $requestValidationErrors
	 */
	public function __construct( Donation $donation = null, array $requestValidationErrors = [] ) {
		$this->donation = $donation;
		$this->validationErrors = $requestValidationErrors;
	}

	public static function newSuccessResponse( Donation $donation ): self {
		return new self( $donation );
	}

	public static function newFailureResponse( array $errors ): self {
		return new self( null, $errors );
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function isSuccessful(): bool {
		return count( $this->validationErrors ) == 0;
	}

	public function getDonation() {
		return $this->donation;
	}

}