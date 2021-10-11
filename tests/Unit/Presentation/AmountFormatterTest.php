<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\AmountFormatter
 *
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AmountFormatterTest extends \PHPUnit\Framework\TestCase {

	public function testGivenGermanLocaleAndFractionalAmount_amountIsFormattedAsGermanString(): void {
		$formatter = new AmountFormatter( 'de_DE' );
		$euro = Euro::newFromCents( 2342 );
		$this->assertEquals( '23,42', $formatter->format( $euro ) );
	}

	public function testGivenGermanLocaleAndIntegerAmount_amountIsFormattedAsGermanString(): void {
		$formatter = new AmountFormatter( 'de_DE' );
		$euro = Euro::newFromInt( 23 );
		$this->assertEquals( '23,00', $formatter->format( $euro ) );
	}

	public function testGivenUSLocaleAndFractionalAmount_amountIsFormattedAsUSString(): void {
		$formatter = new AmountFormatter( 'en_GB' );
		$euro = Euro::newFromCents( 2342 );
		$this->assertSame( '23.42', $formatter->format( $euro ) );
	}

	public function testGivenUSLocaleAndIntegerAmount_amountIsFormattedAsUSString(): void {
		$formatter = new AmountFormatter( 'en_GB' );
		$euro = Euro::newFromInt( 23 );
		$this->assertSame( '23.00', $formatter->format( $euro ) );
	}

	public function testGivenUnknownLocale_exceptionIsThrown(): void {
		$formatter = new AmountFormatter( 'foo_bar' );
		$euro = Euro::newFromInt( 23 );

		$this->expectException( \RuntimeException::class );
		$formatter->format( $euro );
	}

}
