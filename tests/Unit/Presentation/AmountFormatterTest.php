<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\AmountFormatter
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AmountFormatterTest extends \PHPUnit_Framework_TestCase {

	public function testGivenGermanLocaleAndFractionalAmount_amountIsFormattedAsGermanString() {
		$formatter = new AmountFormatter( 'de_DE' );
		$euro = Euro::newFromCents( 2342 );
		$this->assertEquals( '23,42', $formatter->format( $euro ) );
	}

	public function testGivenGermanLocaleAndIntegerAmount_amountIsFormattedAsGermanString() {
		$formatter = new AmountFormatter( 'de_DE' );
		$euro = Euro::newFromInt( 23 );
		$this->assertEquals( '23,00', $formatter->format( $euro ) );
	}

	public function testGivenUSLocaleAndFractionalAmount_amountIsFormattedAsUSString() {
		$formatter = new AmountFormatter( 'en_US' );
		$euro = Euro::newFromCents( 2342 );
		$this->assertEquals( '23.42', $formatter->format( $euro ) );
	}

	public function testGivenUSLocaleAndIntegerAmount_amountIsFormattedAsUSString() {
		$formatter = new AmountFormatter( 'en_US' );
		$euro = Euro::newFromInt( 23 );
		$this->assertEquals( '23.00', $formatter->format( $euro ) );
	}

	public function testGivenUnknownLocale_exceptionIsThrown() {
		$formatter = new AmountFormatter( 'foo_bar' );
		$euro = Euro::newFromInt( 23 );

		$this->expectException( \RuntimeException::class );
		$formatter->format( $euro );
	}

}
