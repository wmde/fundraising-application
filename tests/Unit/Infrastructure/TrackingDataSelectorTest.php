<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

#[CoversClass( TrackingDataSelector::class )]
class TrackingDataSelectorTest extends TestCase {

	/**
	 * @param string $expectedResult
	 * @param string[] $values
	 */
	#[DataProvider( 'preferredValueProvider' )]
	public function testGetPreferredValueReturnsFirstSetElementOrEmptyString( string $expectedResult, array $values ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( $values );
		$this->assertSame( $expectedResult, $value );
	}

	/**
	 * @return array<int, string|string[]>[]
	 */
	public static function preferredValueProvider(): array {
		return [
			[ 'chocolate', [ 'chocolate', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'hazelnuts', [ '', 'hazelnuts', 'campaign/keyword' ] ],
			[ 'campaign/keyword', [ '', '', 'campaign/keyword' ] ],
			[ '', [ '', '', '' ] ],
		];
	}

	#[DataProvider( 'trackingVarProvider' )]
	public function testConcatTrackingFromVarTuple( string $expectedResult, string $campaign, string $keyword ): void {
		$value = TrackingDataSelector::getFirstNonEmptyValue( [
			'',
			'',
			TrackingDataSelector::concatTrackingFromVarTuple( $campaign, $keyword )
		] );

		$this->assertSame( $expectedResult, $value );
	}

	/**
	 * @return string[][]
	 */
	public static function trackingVarProvider(): array {
		return [
			[ 'campaign/keyword', 'campaign', 'keyword' ],
			[ 'campaign/keyword', 'Campaign', 'Keyword' ],
			[ 'campaign', 'campaign', '' ],
			[ '', '', 'keyword' ],
		];
	}

}
