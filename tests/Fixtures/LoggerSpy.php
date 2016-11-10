<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggerSpy extends \WMDE\PsrLogTestDoubles\LoggerSpy {

	public function assertCalledOnceWithMessage( string $expectedMessage, \PHPUnit_Framework_TestCase $testCase ) {
		$testCase->assertEquals(
			[ $expectedMessage ],
			array_map(
				function( array $logCall ) {
					return $logCall['message'];
				},
				$this->getLogCalls()
			),
			'Should be called once with expected message'
		);
	}

}
