<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation;

class ValidationErrorRenderer {

	/**
	 * @param array<string, string> $error
	 */
	public static function render( array $error ): string {
		return sprintf(
			'Error in JSON value "%s": %s',
			$error['pointer'],
			$error['message']
		);
	}
}
