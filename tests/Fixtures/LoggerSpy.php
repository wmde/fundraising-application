<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Psr\Log\AbstractLogger;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggerSpy extends AbstractLogger {

	private $logCalls = [];

	public function log( $level, $message, array $context = [] ) {
		$this->logCalls[] = [ $level, $message, $context ];
	}

	public function getLogCalls(): array {
		return $this->logCalls;
	}

	public function assertNoCalls() {
		if ( !empty( $this->logCalls ) ) {
			throw new \RuntimeException(
				'Logger calls where made while non where expected: ' . var_export( $this->logCalls, true )
			);
		}
	}

}
