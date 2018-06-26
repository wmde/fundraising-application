<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;

class ValidationErrorLogger {

	private $errors = [];

	public function addError( string $error, Campaign $campaign = null ): void {
		if ( $campaign ) {
			$error = $campaign->getName() . ': ' . $error;
		}
		$this->errors[] = $error;
	}

	public function getErrors(): array {
		return $this->errors;
	}

	public function hasErrors(): bool {
		return empty( $this->getErrors() );
	}
}