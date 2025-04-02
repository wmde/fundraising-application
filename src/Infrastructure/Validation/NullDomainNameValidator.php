<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use WMDE\FunValidators\DomainNameValidator;

class NullDomainNameValidator implements DomainNameValidator {

	/**
	 * @param string $domain
	 * @return bool
	 */
	public function isValid( string $domain ): bool {
		return true;
	}

}
