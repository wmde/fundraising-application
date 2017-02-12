<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\AmountParser;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\AmountParser
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AmountParserTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider valueProvider
	 * @param string $locale
	 * @param float $expectedValue
	 * @param string $inputValue
	 */
	public function testGivenFormattedString_setAmountFromStringParsesIntoFloat( $locale, $expectedValue, $inputValue ) {
		$this->assertSame(
			$expectedValue,
			( new AmountParser( $locale ) )->parseAsFloat( $inputValue )
		);
	}

	public function valueProvider() {
		return [
			[ 'de_DE', 29.5, '29,50' ],
			[ 'de_DE', 0.1, '0,10' ],
			[ 'de_DE', 1234.56, '1234,56' ],
			[ 'de_DE', 1234567.89, '1.234.567,89' ],
			[ 'de_DE', 0.0, '0' ],
			[ 'de_DE', 0.0, '' ],
			[ 'de_DE', 0.0, 'abc' ],

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
