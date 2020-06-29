<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class supports reading deprecated parameter values from an HTTP request
 *
 * It should be removed after January 2021
 *
 * @package WMDE\Fundraising\Frontend\Infrastructure
 */
class FallbackRequestValueReader {
	private $logger;
	private $request;

	public function __construct( LoggerInterface $logger, Request $request ) {
		$this->logger = $logger;
		$this->request = $request;
	}

	public function getAmount(): int {
		$deprecatedParameterNames = [ 'betrag', 'betrag_auswahl', 'amountGiven' ];
		foreach ( $deprecatedParameterNames as $deprecatedParameterName ) {
			if ( $this->request->get( $deprecatedParameterName ) !== null ) {
				$this->logDeprecationWarning( $deprecatedParameterName );
				// convert German-formatted numbers to English
				return intval( round( floatval( str_replace( ',', '.', $this->request->get( $deprecatedParameterName ) ) ) * 100 ) );
			}
		}
		return 0;
	}

	public function getPaymentType(): string {
		if ( $this->request->get( 'zahlweise' ) !== null ) {
			$this->logDeprecationWarning( 'zahlweise' );
			return (string)$this->request->get( 'zahlweise', '' );
		}
		return '';
	}

	public function getInterval(): ?int {
		if ( $this->request->get( 'periode' ) !== null ) {
			$this->logDeprecationWarning( 'periode' );
			return intval( $this->request->get( 'periode' ) );
		}
		return null;
	}

	private function logDeprecationWarning( string $deprecatedParameterName ): void {
		$this->logger->warning(
			"Some application is still submitting the deprecated form parameter '{$deprecatedParameterName}'",
			[ 'referrer' => $this->request->headers->get( 'referer' ) ]
		);
	}

}
