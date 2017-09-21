<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\LessSimpleTransferCodeGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\LessSimpleTransferCodeGenerator
 *
 * @licence GNU GPL v2+
 */
class LessSimpleTransferCodeGeneratorTest extends TestCase {

	/**
	 * @dataProvider characterAndCodeProvider
	 */
	public function testGenerateBankTransferCode( string $expectedCode, string $usedCharacters ): void {
		$generator = LessSimpleTransferCodeGenerator::newDeterministicGenerator(
			$this->newFixedCharacterGenerator( $usedCharacters )
		);

		$this->assertSame( $expectedCode, $generator->generateTransferCode() );
	}

	public function characterAndCodeProvider(): iterable {
		yield [ 'ACD-EFK-X', 'ACDEFKLMNPRSTWXYZ349ACDEF' ];
		yield [ 'AAA-AAA-D', 'AAAAAAAAAAAAAAAAAAAAAAAAA' ];
		yield [ 'CAA-AAA-S', 'CAAAAAAAAAAAAAAAAAAAAAAAA' ];
		yield [ 'ACA-CAC-L', 'ACACACACACACACACACACACACA' ];
	}

	private function newFixedCharacterGenerator( string $characters ): \Generator {
		yield from str_split( $characters );
	}

	/**
	 * @dataProvider invalidCodeProvider
	 */
	public function testInvalidTransferCodesAreNotValid( string $invalidCode ): void {
		$this->assertFalse( $this->newGenerator()->transferCodeIsValid( $invalidCode ) );
	}

	private function newGenerator(): LessSimpleTransferCodeGenerator {
		return LessSimpleTransferCodeGenerator::newDeterministicGenerator(
			$this->newFixedCharacterGenerator( 'ACDEFKLMNPRSTWXYZ349' )
		);
	}

	public function invalidCodeProvider(): iterable {
		yield 'Empty code' => [ '' ];
		yield 'Without checksum' => [ 'ACD-EFK-' ];
		yield 'Missing dash' => [ 'ACDEFK-X' ];
		yield 'Missing checksum dash' => [ 'ACD-EFK' ];
		yield 'Missing both dashes' => [ 'ACDEFK' ];
		yield 'Extra dash' => [ 'ACD-EFK-X-' ];
		yield 'Extra character' => [ 'ACD-EFKK-X' ];
		yield 'Extra checksum character' => [ 'ACD-EFK-XX' ];
		yield 'Not allowed character' => [ '0CD-EFK-X' ];
		yield 'Extra character at front' => [ 'AACD-EFK-X' ];
		yield 'Invalid checksum' => [ 'ACD-EFK-A' ];
	}

	/**
	 * @dataProvider characterAndCodeProvider
	 */
	public function testValidTransferCodesAreValid( string $transferCode ): void {
		$this->assertTrue( $this->newGenerator()->transferCodeIsValid( $transferCode ) );
	}

	public function testRandomGeneratorProducesValidCodes(): void {
		$generator = LessSimpleTransferCodeGenerator::newRandomGenerator();

		for ( $i = 0; $i < 42; $i++ ) {
			$this->assertTrue( $generator->transferCodeIsValid( $generator->generateTransferCode() ) );
		}
	}

}
