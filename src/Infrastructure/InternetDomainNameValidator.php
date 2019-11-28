<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\FunValidators\DomainNameValidator;

/**
 * @license GNU GPL v2+
 */
class InternetDomainNameValidator implements DomainNameValidator {

	private const BANNED_DOMAINS = [
		'example.com'
	];

	public function isValid( string $domain ): bool {
		return !$this->isDomainBanned( $domain ) && $this->domainHasDnsEntry( $domain );
	}

	private function isDomainBanned( string $domain ): bool {
		return in_array( strtolower( trim( $domain ) ), self::BANNED_DOMAINS );
	}

	private function domainHasDnsEntry( string $domain ): bool {
		return checkdnsrr( $domain, 'MX' ) || checkdnsrr( $domain, 'A' ) || checkdnsrr( $domain, 'SOA' );
	}

}
