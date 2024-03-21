<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector
 */
class TrackingDataSelectorTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider preferredValueProvider
	 *
	 * @param string $expectedResult
	 * @param string[] $values
	 */
	public function testGetPreferredValueReturnsFirstSetElementOrEmptyString( string $expectedResult, array $values ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( $values );
		$this->assertSame( $expectedResult, $value );
	}

	public static function preferredValueProvider(): array {
		return [
			[ 'chocolate', [ 'chocolate', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'hazelnuts', [ '', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'campaign/keyword', [ '', '', 'campaign/keyword' ] ],
			[ '', [ '', '', '' ] ],
		];
	}

	/**
	 * @dataProvider trackingVarProvider
	 */
	public function testConcatTrackingFromVarCouple( string $expectedResult, string $campaign, string $keyword ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( [
			'',
			'',
			TrackingDataSelector::concatTrackingFromVarTuple( $campaign, $keyword )
		] );

		$this->assertSame( $expectedResult, $value );
	}

	public static function trackingVarProvider(): array {
		return [
			[ 'campaign/keyword', 'campaign', 'keyword' ],
			[ 'campaign/keyword', 'Campaign', 'Keyword' ],
			[ 'campaign', 'campaign', '' ],
			[ '', '', 'keyword' ],
		];
	}

}
