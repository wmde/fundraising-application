<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
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

	/**
	 * @var string|null
	 */
	private $accessToken = null;

	public static function newSuccessResponse( Donation $donation, string $updateToken, string $accessToken ): self {
		$response = new self();
		$response->donation = $donation;
		$response->updateToken = $updateToken;
		$response->accessToken = $accessToken;
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
	 * WARNING: we're returning the domain object to not have to create a  more verbose response model.
	 * Keep in mind that you should not use domain logic in the presenter, or put presentation helpers
	 * in the domain object!
	 *
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

	/**
	 * @return string|null
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

}
