<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use Psr\Log\LoggerInterface;
use WMDE\FunValidators\ConstraintViolation;

class ValidationErrorLogger {

	private LoggerInterface $logger;
	private const LEGACY_FIELDS = [ 'betrag', 'betrag_auswahl', 'periode', 'zahlweise' ];

	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	public function logViolations( string $message, array $fields, array $validationErrors ): void {
		if ( $this->hasMoreErrorsThan( 4, $fields ) ) {
			return;
		}

		if ( $this->containsLegacyFields( $fields ) ) {
			return;
		}

		$this->logger->warning( $message, $validationErrors );
	}

	private function hasMoreErrorsThan( int $number, array $violations ): bool {
		return count( $violations ) > $number;
	}

	private function containsLegacyFields( array $violations ) {
		foreach ( $violations as $violation ) {
			if ( in_array( $violation, self::LEGACY_FIELDS ) ) {
				return true;
			}
		}

		return false;
	}
}
