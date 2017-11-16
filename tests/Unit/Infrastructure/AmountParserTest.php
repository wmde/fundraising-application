<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\AmountParser;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\AmountParser
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AmountParserTest extends TestCase {

	/**
	 * @dataProvider valueProvider
	 */
	public function testGivenFormattedString_setAmountFromStringParsesIntoFloat( string $locale, float $expectedValue, string $inputValue ): void {
		$this->assertSame(
			$expectedValue,
			( new AmountParser( $locale ) )->parseAsFloat( $inputValue )
		);
	}

	public function valueProvider(): array {
		return [
			[ 'de_DE', 29.5, '29,50' ],
			[ 'de_DE', 0.1, '0,10' ],
			[ 'de_DE', 1234.56, '1234,56' ],
			[ 'de_DE', 1234567.89, '1.234.567,89' ],
			[ 'de_DE', 0.0, '0' ],
			[ 'de_DE', 0.0, '' ],
			[ 'de_DE', 0.0, 'abc' ],
			[ 'de_DE', 0.0, '17.50' ], // no support for "number as string" when in German mode

			[ 'en_US', 29.5, '29.50' ],
			[ 'en_US', 0.1, '0.10' ],
			[ 'en_US', 1234.56, '1234.56' ],
			[ 'en_US', 1234567.89, '1,234,567.89' ],
			[ 'en_US', 0.0, '0' ],
			[ 'en_US', 0.0, '' ],
			[ 'en_US', 0.0, 'abc' ],
		];
	}

}
