<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use WMDE\FunValidators\DomainNameValidator;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class NullDomainNameValidator implements DomainNameValidator {

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param string $domain
	 * @return bool
	 */
	public function isValid( string $domain ): bool {
		return true;
	}

}
