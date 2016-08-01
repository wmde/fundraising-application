<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationResponse {

	public static function newNotAllowedResponse(): self {
		return new self( null );
	}

	public static function newValidResponse( Donation $donation ): self {
		return new self( $donation );
	}

	private $donation;

	private function __construct( Donation $donation = null ) {
		$this->donation = $donation;
	}

	/**
	 * Returns the Donation when @see accessIsPermitted returns true, or null otherwise.
	 *
	 * @return \WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation|null
	 */
	public function getDonation() {
		return $this->donation;
	}

	public function accessIsPermitted(): bool {
		return $this->donation !== null;
	}

}