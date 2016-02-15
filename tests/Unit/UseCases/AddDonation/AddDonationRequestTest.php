<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationRequestTest extends \PHPUnit_Framework_TestCase {

	public function valueProvider() {
		return [
			[ 'de_DE', 29.5, '29,50' ],
			[ 'de_DE', 0.1, '0,10' ],
			[ 'de_DE', 1234.56, '1234,56' ],
			[ 'de_DE', 1234567.89, '1.234.567,89' ],
			[ 'de_DE', 0, '0' ],
			[ 'de_DE', 0, '' ],
			[ 'de_DE', 0, 'abc' ],

			[ 'en_US', 29.5, '29.50' ],
			[ 'en_US', 0.1, '0.10' ],
			[ 'en_US', 1234.56, '1234.56' ],
			[ 'en_US', 1234567.89, '1,234,567.89' ],
			[ 'en_US', 0, '0' ],
			[ 'en_US', 0, '' ],
			[ 'en_US', 0, 'abc' ],
		];
	}

	/**
	 * @dataProvider valueProvider
	 * @param string $locale
	 * @param float $expectedValue
	 * @param string $inputValue
	 */
	public function testGivenFormattedString_setAmountFromStringParsesIntoFloat( $locale, $expectedValue, $inputValue ) {
		$request = new AddDonationRequest();
		$request->setAmountFromString( $inputValue, $locale );
		$this->assertEquals( $expectedValue, $request->getAmount() );
	}

	/**
	 * @dataProvider preferredValueProvider
	 *
	 * @param string $expectedResult
	 * @param string[] $values
	 */
	public function testGetPreferredValueReturnsFirstSetElementOrEmptyString( $expectedResult, $values ) {
		$value = AddDonationRequest::getPreferredValue( $values );
		$this->assertSame( $expectedResult, $value );
	}

	public function preferredValueProvider() {
		return [
			[ 'chocolate', [ 'chocolate', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'hazelnuts', [ '', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'campaign/keyword', [ '', '', 'campaign/keyword' ] ],
			[ '', [ '', '', '' ] ],
		];
	}

	/**
	 * @dataProvider trackingVarProvider
	 *
	 * @param $expectedResult
	 * @param $campaign
	 * @param $keyword
	 */
	public function testConcatTrackingFromVarCouple( $expectedResult, $campaign, $keyword ) {
		$value = AddDonationRequest::getPreferredValue( [
			'',
			'',
			AddDonationRequest::concatTrackingFromVarCouple( $campaign, $keyword )
		] );

		$this->assertSame( $expectedResult, $value );
	}

	public function trackingVarProvider() {
		return [
			[ 'campaign/keyword', 'campaign', 'keyword' ],
			[ 'campaign/keyword', 'Campaign', 'Keyword' ],
			[ 'campaign', 'campaign', '' ],
			[ '', '', 'keyword' ],
		];
	}

}
