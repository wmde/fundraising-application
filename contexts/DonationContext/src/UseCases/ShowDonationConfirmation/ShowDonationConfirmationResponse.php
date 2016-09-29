<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationResponse {

	public static function newNotAllowedResponse(): self {
		return new self( null );
	}

	public static function newValidResponse( Donation $donation, string $updateToken ): self {
		return new self( $donation, $updateToken );
	}

	private $donation;
	private $updateToken;

	private function __construct( Donation $donation = null, string $updateToken = null ) {
		$this->donation = $donation;
		$this->updateToken = $updateToken;
	}

	/**
	 * Returns the Donation when @see accessIsPermitted returns true, or null otherwise.
	 *
	 * @return Donation|null
	 */
	public function getDonation() {
		return $this->donation;
	}

	public function accessIsPermitted(): bool {
		return $this->donation !== null;
	}

	/**
	 * @return string|null
	 */
	public function getUpdateToken() {
		return $this->updateToken;
	}

}