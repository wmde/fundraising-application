<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presenters;

use WMDE\Fundraising\Frontend\Presenters\GreetingGenerator;

class GreetingGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoLastName_neutralGreetingIsGenerated() {
		$generator = new GreetingGenerator();
		$this->assertSame( 'Sehr geehrte Damen und Herren,', $generator->createGreeting( '', 'Herr', '' ) );
	}

	public function testGivenNoSalutation_neutralGreetingIsGenerated() {
		$generator = new GreetingGenerator();
		$this->assertSame( 'Sehr geehrte Damen und Herren,', $generator->createGreeting( 'Nyan', '', '' ) );
	}

	/**
	 * @dataProvider greetingProvider
	 */
	public function testGivenASalutation_specificGreetingIsGenerated( $salutation, $expected) {
		$generator = new GreetingGenerator();
		$this->assertSame( $expected, $generator->createGreeting( 'Nyan', $salutation, '' ) );
	}

	public function greetingProvider() {
		return [
			[ 'Herr', 'Sehr geehrter Herr Nyan,' ],
			[ 'Frau', 'Sehr geehrte Frau Nyan,' ],
			[ 'Familie', 'Sehr geehrte Familie Nyan,' ]
		];
	}

	/**
	 * @dataProvider greetingTitleProvider
	 */
	public function testGivenATitle_itIsMentionInGreeting( $salutation, $title, $expected) {
		$generator = new GreetingGenerator();
		$this->assertSame( $expected, $generator->createGreeting( 'Nyan', $salutation, $title ) );
	}

	public function greetingTitleProvider() {
		return [
			[ 'Herr', 'Dr.', 'Sehr geehrter Herr Dr. Nyan,' ],
			[ 'Frau', 'Prof.', 'Sehr geehrte Frau Prof. Nyan,' ],
			[ 'Familie', 'Dr.', 'Sehr geehrte Familie Dr. Nyan,' ],
			[ '', 'Prof. Dr.', 'Sehr geehrte Damen und Herren,' ]
		];
	}
}
