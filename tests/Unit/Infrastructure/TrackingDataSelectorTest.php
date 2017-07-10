<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Unit\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TrackingDataSelectorTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider preferredValueProvider
	 *
	 * @param string $expectedResult
	 * @param string[] $values
	 */
	public function testGetPreferredValueReturnsFirstSetElementOrEmptyString( string $expectedResult, $values ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( $values );
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
	public function testConcatTrackingFromVarCouple( $expectedResult, $campaign, $keyword ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( [
			'',
			'',
			TrackingDataSelector::concatTrackingFromVarTuple( $campaign, $keyword )
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
