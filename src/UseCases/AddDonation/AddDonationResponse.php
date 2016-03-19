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

	/**
	 * @var ConstraintViolation[]
	 */
	private $validationErrors = [];

	/**
	 * @var Donation|null
	 */
	private $donation = null;

	/**
	 * @var string|null
	 */
	private $updateToken = null;

	public static function newSuccessResponse( Donation $donation, string $updateToken ): self {
		$response = new self();
		$response->donation = $donation;
		$response->updateToken = $updateToken;
		return $response;
	}

	public static function newFailureResponse( array $errors ): self {
		$response = new self();
		$response->validationErrors = $errors;
		return $response;
	}

	private function __construct() {
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function isSuccessful(): bool {
		return empty( $this->validationErrors );
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
