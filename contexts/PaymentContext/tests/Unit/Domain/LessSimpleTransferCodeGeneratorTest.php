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
		$generator = new LessSimpleTransferCodeGenerator(
			$this->newFixedCharacterGenerator( $usedCharacters )
		);

		$this->assertSame( $expectedCode, $generator->generateTransferCode() );
	}

	public function characterAndCodeProvider(): iterable {
		yield [ 'ACDE-FKLM-X', 'ACDEFKLMNPRSTWXYZ349ACDEF' ];
		yield [ 'AAAA-AAAA-L', 'AAAAAAAAAAAAAAAAAAAAAAAAA' ];
		yield [ 'CAAA-AAAA-9', 'CAAAAAAAAAAAAAAAAAAAAAAAA' ];
		yield [ 'ACAC-ACAC-K', 'ACACACACACACACACACACACACA' ];
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
		return new LessSimpleTransferCodeGenerator(
			$this->newFixedCharacterGenerator( 'ACDEFKLMNPRSTWXYZ349' )
		);
	}

	public function invalidCodeProvider(): iterable {
		yield 'Empty code' => [ '' ];
		yield 'Without checksum' => [ 'ACDE-FKLM-' ];
		yield 'Missing dash' => [ 'ACDEFKLM-X' ];
		yield 'Missing checksum dash' => [ 'ACDE-FKLMX' ];
		yield 'Missing both dashes' => [ 'ACDEFKLMX' ];
		yield 'Extra dash' => [ 'ACDE-FKLM-X-' ];
		yield 'Extra character' => [ 'ACDE-FKLMM-X' ];
		yield 'Extra checksum character' => [ 'ACDE-FKLM-XX' ];
		yield 'Not allowed character' => [ '0CDE-FKLM-X' ];
		yield 'Extra character at front' => [ 'AACDE-FKLM-X' ];
		yield 'Invalid checksum' => [ 'ACDE-FKLM-A' ];
	}

	/**
	 * @dataProvider characterAndCodeProvider
	 */
	public function testValidTransferCodesAreValid( string $transferCode ): void {
		$this->assertTrue( $this->newGenerator()->transferCodeIsValid( $transferCode ) );
	}

}
