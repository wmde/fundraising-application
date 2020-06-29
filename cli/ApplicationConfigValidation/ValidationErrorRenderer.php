<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation;

/**
 * @license GPL-2.0-or-later
 */
class ValidationErrorRenderer {

	public static function render( array $error ): string {
		return sprintf(
			'Error in JSON value "%s": %s',
			$error['pointer'],
			$error['message']
		);
	}
}
