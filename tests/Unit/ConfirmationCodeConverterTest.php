<?php


namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\ConfirmationCodeConverter;

class ConfirmationCodeConverterTest extends \PHPUnit_Framework_TestCase {

	public function testGivenABinaryString_itIsConvertedToReadableFormat() {
		$binaryString = chr( 42 ) . chr( 24 ) . chr( 13 ) . chr( 37 );
		$converter = new ConfirmationCodeConverter();
		$this->assertSame( 'bogqat', $converter->fromBinaryToReadable( $binaryString ) );
	}

	public function testGivenReadableString_itIsConvertedToBinaryData() {
		$converter = new ConfirmationCodeConverter();
		$binaryString = chr( 97 ) . chr( 193 ) . chr( 23 ) . chr( 9 ) . chr( 180 ) . chr( 120 );
		$this->assertSame( $binaryString, $converter->fromReadableToBinary( '123kittens' ) );
	}

	public function testGivenReadableStringWithBogusCharacters_itIsConvertedToBinaryData() {
		$converter = new ConfirmationCodeConverter();
		$binaryString = chr( 97 ) . chr( 193 ) . chr( 23 ) . chr( 9 ) . chr( 180 ) . chr( 120 );
		$this->assertSame( $binaryString, $converter->fromReadableToBinary( '123-kittens!!!' ) );
	}

}
