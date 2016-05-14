<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Psr\Log\AbstractLogger;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggerSpy extends AbstractLogger {

	const LEVEL_INDEX = 0;
	const MESSAGE_INDEX = 1;
	const CONTEXT_INDEX = 2;

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

	public function assertCalledOnceWithMessage( string $expectedMessage, \PHPUnit_Framework_TestCase $testCase ) {
		$testCase->assertEquals(
			[ $expectedMessage ],
			array_map(
				function( array $logCall ) {
					return $logCall[self::MESSAGE_INDEX];
				},
				$this->logCalls
			),
			'Should be called once with expected message'
		);
	}

}
