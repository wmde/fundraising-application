<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use WMDE\Fundraising\Frontend\Domain\DomainNameValidator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InternetDomainNameValidator implements DomainNameValidator {

	public function isValid( string $domain ): bool {
		return checkdnsrr( $domain, 'MX' ) || checkdnsrr( $domain, 'A' );
	}

}
