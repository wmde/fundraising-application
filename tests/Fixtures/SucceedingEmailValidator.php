<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\Validation\NullDomainNameValidator;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\EmailValidator;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingEmailValidator extends EmailValidator {

	public function __construct() {
		parent::__construct( new NullDomainNameValidator() );
	}

	public function validate( string $emailAddress ): ValidationResult {	// @codingStandardsIgnoreLine
		return new ValidationResult();
	}

}
