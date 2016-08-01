<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class NullDomainNameValidator implements DomainNameValidator {

	public function isValid( string $domain ): bool {
		return true;
	}

}
