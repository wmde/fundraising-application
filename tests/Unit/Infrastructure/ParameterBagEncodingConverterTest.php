<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use WMDE\Fundraising\Frontend\Infrastructure\ParameterBagEncodingConverter;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\ParameterBagEncodingConverter
 */
class ParameterBagEncodingConverterTest extends TestCase {
	public function testGivenEmptyParameterBagThenResultIsEmpty(): void {
		$input = new ParameterBag();

		$result = ParameterBagEncodingConverter::convert( $input, 'ISO-8859-1' );

		$this->assertSame( [], $result->all() );
	}

	public function testGivenIsoLatin1EncodedParameterBagThenResultIsConverted(): void {
		$input = new ParameterBag( [ 'Encoded_Value' => "Bitte \xe4ndern" ] );

		$result = ParameterBagEncodingConverter::convert( $input, 'ISO-8859-1' );

		$this->assertSame( 'Bitte ändern', $result->get( 'Encoded_Value' ) );
	}

	/**
	 * @dataProvider typedValues
	 */
	public function testGivenNonStringInParameterBagThenValueShouldBeUntouched( mixed $value ): void {
		$input = new ParameterBag( ['some_value' => $value] );

		$result = ParameterBagEncodingConverter::convert( $input, 'ISO-8859-1' );

		$this->assertSame( $value, $result->get('some_value') );
	}

	/**
	 * @return iterable<string,mixed>
	 */
	public function typedValues(): iterable {
		yield 'Numeric String' => [ '44' ];
		yield 'Int' => [ 27 ];
		yield 'Float' => [ 3.14 ];
		yield 'Boolean' => [ false ];
		yield 'null' => [ null ];
	}
}
