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
	private $updateToken;

	/**
	 * @param Donation|null $donation
	 * @param string|null $updateToken
	 * @param ConstraintViolation[] $requestValidationErrors
	 */
	private function __construct( Donation $donation = null, string $updateToken = null, array $requestValidationErrors = [] ) {
		$this->donation = $donation;
		$this->validationErrors = $requestValidationErrors;
		$this->updateToken = $updateToken;
	}

	public static function newSuccessResponse( Donation $donation, string $updateToken ): self {
		return new self( $donation, $updateToken );
	}

	public static function newFailureResponse( array $errors ): self {
		return new self( null, null, $errors );
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

	/**
	 * @return Donation|null
	 */
	public function getDonation() {
		return $this->donation;
	}

	/**
	 * @return string|null
	 */
	public function getUpdateToken() {
		return $this->updateToken;
	}

}