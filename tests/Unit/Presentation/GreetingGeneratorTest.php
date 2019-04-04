<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;

class GreetingGeneratorTest extends TestCase {

	public function testGivenNoLastNameForFormalGreeting_neutralGreetingIsGenerated(): void {
		$this->assertSame( 'mail_introduction_generic', $this->getGreetingGenerator()->createFormalGreeting( '', 'Herr', '' ) );
	}

	public function testGivenNoSalutationForFormalGreeting_neutralGreetingIsGenerated(): void {
		$this->assertSame( 'mail_introduction_generic', $this->getGreetingGenerator()->createFormalGreeting( 'Nyan', '', '' ) );
	}

	/**
	 * @dataProvider formalGreetingProvider
	 */
	public function testGivenASalutationForFormalGreeting_specificGreetingIsGenerated( string $salutation, string $expected ): void {
		$this->assertSame( $expected, $this->getGreetingGenerator()->createFormalGreeting( 'Nyan', $salutation, '' ) );
	}

	public function formalGreetingProvider(): array {
		return [
			[ 'Herr', 'mail_introduction_male_formal' ],
			[ 'Frau', 'mail_introduction_female_formal' ],
			[ 'Familie', 'mail_introduction_family_formal' ]
		];
	}

	public function testGivenNoFirstNameForInformalPersonalGreeting_neutralGreetingIsGenerated(): void {
		$this->assertSame( 'mail_introduction_generic', $this->getGreetingGenerator()->createInformalGreeting( 'Herr', '', 'Zuse' ) );
	}

	public function testGivenNoLastNameForInformalFamilyGreeting_neutralGreetingIsGenerated(): void {
		$this->assertSame( 'mail_introduction_generic', $this->getGreetingGenerator()->createInformalGreeting( 'Familie', 'Konrad', '' ) );
	}

	public function testGivenNoSalutationForInformalGreeting_neutralGreetingIsGenerated(): void {
		$this->assertSame( 'mail_introduction_generic', $this->getGreetingGenerator()->createInformalGreeting( '', 'Testy', 'MacTest' ) );
	}

	/**
	 * @dataProvider informalGreetingProvider
	 */
	public function testGivenASalutationForInformalGreeting_specificGreetingIsGenerated( string $salutation, string $expected ): void {
		$this->assertSame( $expected, $this->getGreetingGenerator()->createInformalGreeting( $salutation, 'Sascha', 'Mustermann' ) );
	}

	public function informalGreetingProvider(): array {
		return [
			[ 'Herr', 'mail_introduction_male_informal' ],
			[ 'Frau', 'mail_introduction_female_informal' ],
			[ 'Familie', 'mail_introduction_family_informal' ]
		];
	}

	private function getGreetingGenerator(): GreetingGenerator {
		return new GreetingGenerator( new Translator( 'zz_ZZ' ) );
	}
}
