<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Tests\Unit\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonatingContext\UseCases\AddDonation\AddDonationRequest;

/**
 * @covers WMDE\Fundraising\Frontend\DonatingContext\UseCases\AddDonation\AddDonationRequest
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationRequestTest extends \PHPUnit_Framework_TestCase {

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
