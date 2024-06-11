<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use Psr\Log\LoggerInterface;

class ValidationErrorLogger {

	public function __construct( private readonly LoggerInterface $logger ) {
	}

	/**
	 * @param string $message
	 * @param string[] $fields
	 * @param array<string, array<int, string>> $validationErrors
	 */
	public function logViolations( string $message, array $fields, array $validationErrors ): void {
		if ( $this->hasMoreErrorsThan( 4, $fields ) ) {
			return;
		}

		$this->logger->warning( $message, $validationErrors );
	}

	/**
	 * @param int $number
	 * @param string[] $violations
	 */
	private function hasMoreErrorsThan( int $number, array $violations ): bool {
		return count( $violations ) > $number;
	}
}
