<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Presentation\Salutations;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Translation\GreetingGenerator
 */
class GreetingGeneratorTest extends TestCase {

	/**
	 * @dataProvider formalGreetingProvider
	 */
	public function testSpecificFormalGreetingIsGenerated( string $salutation, string $expected ): void {
		$this->assertSame(
			$expected,
			$this->getGreetingGenerator()->createFormalGreeting( $salutation, firstName: 'Nyan', lastName: 'Cat', title: '' )
		);
	}

	public function testGivenNoLastNameForFormalGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createFormalGreeting( salutation: 'Herr', firstName: 'Nyan', lastName: '', title: '' )
		);
	}

	public function testGivenUnknownSalutationForFormalGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createFormalGreeting( salutation: '', firstName: 'Nyan', lastName: 'Cat', title: '' )
		);
	}

	/**
	 * @dataProvider informalGreetingProvider
	 */
	public function testSpecificInformalGreetingIsGenerated( string $salutation, string $expected ): void {
		$this->assertSame(
			$expected,
			$this->getGreetingGenerator()->createInformalGreeting( $salutation, firstName: 'Sascha', lastName: 'Mustermann' )
		);
	}

	public function testGivenNoFirstNameForInformalGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createInformalGreeting( salutation: 'Herr', firstName: '', lastName: 'Zuse' )
		);
	}

	public function testGivenUnknownSalutationForInformalGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createInformalGreeting( salutation: 'Sky Pirate', firstName: 'Testy', lastName: 'MacTest' )
		);
	}

	/**
	 * @dataProvider informalLastnameGreetingProvider
	 */
	public function testSpecificInformalLastNameGreetingIsGenerated( string $salutation, string $expected ): void {
		$this->assertSame(
			$expected,
			$this->getGreetingGenerator()->createInformalLastnameGreeting( $salutation, firstName: '', lastName: 'Mustermann', title: '' )
		);
	}

	public function testGivenNoLastnameForInformalLastnameGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createInformalLastnameGreeting( salutation: 'Herr', firstName: 'Nyan', lastName: '', title: '' )
		);
	}

	public function testGivenUnknownSalutationForInformalLastnameGreeting_genericGreetingIsGenerated(): void {
		$this->assertSame(
			'genericGreeting',
			$this->getGreetingGenerator()->createInformalLastnameGreeting( salutation: 'Dark Priest', firstName: 'Testname', lastName: '', title: '' )
		);
	}

	public function formalGreetingProvider(): array {
		return [
			[ 'Herr', 'formalHerr' ],
			[ 'Frau', 'formalFrau' ],
			[ 'Divers', 'formalDivers' ]
		];
	}

	public function informalGreetingProvider(): array {
		return [
			[ 'Herr', 'informalHerr' ],
			[ 'Frau', 'informalFrau' ],
			[ 'Divers', 'informalDivers' ]
		];
	}

	public function informalLastnameGreetingProvider(): array {
		return [
			[ 'Herr', 'lastNameInformalHerr' ],
			[ 'Frau', 'lastNameInformalFrau' ],
			[ 'Divers', 'lastNameInformalDivers' ]
		];
	}

	private function getGreetingGenerator(): GreetingGenerator {
		return new GreetingGenerator( new FakeTranslator(), new Salutations( [
			[
				'label' => 'Herr',
				'value' => 'Herr',
				'display' => 'Herr',
				'greetings' => [
					'formal' => 'formalHerr',
					'informal' => 'informalHerr',
					'lastNameInformal' => 'lastNameInformalHerr',
				],
			],
			[
				'label' => 'Frau',
				'value' => 'Frau',
				'display' => 'Frau',
				'greetings' => [
					'formal' => 'formalFrau',
					'informal' => 'informalFrau',
					'lastNameInformal' => 'lastNameInformalFrau',
				],
			],
			[
				'label' => 'Divers',
				'value' => 'Divers',
				'display' => '',
				'greetings' => [
					'formal' => 'formalDivers',
					'informal' => 'informalDivers',
					'lastNameInformal' => 'lastNameInformalDivers',
				],
			]
		] ), 'genericGreeting' );
	}
}
