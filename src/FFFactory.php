<?php

namespace WMDE\Fundraising\Frontend;

use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FFFactory {

	public static function newFromConfig() {
		return new self();
	}

	private function __construct() {
	}

	public function newValidateEmailUseCase(): ValidateEmailUseCase {
		return new ValidateEmailUseCase();
	}

}