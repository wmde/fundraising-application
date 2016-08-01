<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ShowMembershipApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\MembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipAppConfirmationResponse {

	private $membershipApplication;
	private $updateToken;

	public static function newNotAllowedResponse(): self {
		return new self();
	}

	public static function newValidResponse( MembershipApplication $membershipApplication, string $updateToken ): self {
		return new self( $membershipApplication, $updateToken );
	}

	private function __construct( MembershipApplication $membershipApplication = null, string $updateToken = null ) {
		$this->membershipApplication = $membershipApplication;
		$this->updateToken = $updateToken;
	}

	/**
	 * Returns the MembershipApplication when @see accessIsPermitted returns true, or null otherwise.
	 *
	 * @return MembershipApplication|null
	 */
	public function getApplication() {
		return $this->membershipApplication;
	}

	public function getUpdateToken() {
		return $this->updateToken;
	}

	public function accessIsPermitted(): bool {
		return $this->membershipApplication !== null;
	}

}