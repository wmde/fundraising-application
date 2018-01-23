<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface MembershipTokenGenerator {

	public function generateToken(): string;

	public function generateTokenExpiry(): \DateTime;

}
