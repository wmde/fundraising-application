<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;
use PHPUnit\Framework\TestCase;

class CampaignDateTest extends TestCase {

	public function testGivenNoTimezone_newInstanceHasUTC() {
		$systemTimezone = date_default_timezone_get();
		date_default_timezone_set( 'Asia/Almaty' );

		$campaignDate = new CampaignDate();

		$this->assertSame( 'UTC', $campaignDate->getTimezone()->getName() );

		// restore system time zone
		date_default_timezone_set( $systemTimezone );
	}

	public function testGivenTimezone_itThrowsExceptionIfNotUTC() {
		$this->expectException( \InvalidArgumentException::class );

		new CampaignDate( 'now', new \DateTimeZone( 'Europe/Berlin' ) );
	}

	public function testCreateFromString() {
		$dateStr = '1987-11-23 03:14:00';

		$campaignDate = CampaignDate::createFromString( $dateStr );

		$this->assertSame( $dateStr, $campaignDate->format( 'Y-m-d H:i:s' ) );
	}

	public function testCreateFromStringWithTimezoneConvertsToUtc() {
		$dateStr = '1987-11-22 21:14:00';

		$campaignDate = CampaignDate::createFromString( $dateStr, new \DateTimeZone( 'America/Chicago' ) );

		$this->assertSame( '1987-11-23 03:14:00', $campaignDate->format( 'Y-m-d H:i:s' ) );
	}

}
