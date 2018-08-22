<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\StreamBucketLogWriter;

class StreamBucketLogWriterTest extends TestCase {

	public function testLogWriterAddsDate() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogWriter( $logfile );
		$logWriter->setDateFormat( 'Y-m-d H:i' ); // Ignore seconds to avoid Heisentests

		$logWriter->writeEvent( 'testLogged', [], ...[] );

		$this->assertLogValue( date( 'Y-m-d H:i' ), 'date', $logfile );
	}

	public function testGivenEventName_itIsLogged() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogWriter( $logfile );

		$logWriter->writeEvent( 'testLogged', [], ...[] );

		$this->assertLogValue( 'testLogged', 'eventName', $logfile );
	}

	public function testGivenEventMetadata_itIsLogged() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogWriter( $logfile );

		$logWriter->writeEvent( 'donationCreated', [ 'id' => 1, 'name' => 'Kari Nordmann' ], ...[] );

		$this->assertLogValue( [ 'id' => 1, 'name' => 'Kari Nordmann' ], 'metadata', $logfile );
	}

	public function testGivenBuckets_theyAreOutputWithTheirCampaigns() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogWriter( $logfile );
		$campaign1 = new Campaign( 'test1', 't1', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$firstCampaignBucket = new Bucket( 'first', $campaign1, true );
		$campaign1->addBucket( $firstCampaignBucket );
		$campaign2 = new Campaign( 'test2', 't2', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$secondCampaignBucket = new Bucket( 'second', $campaign2, true );
		$campaign2->addBucket( $secondCampaignBucket );

		$logWriter->writeEvent( 'donationCreated', [ 'id' => 1, 'name' => 'Kari Nordmann' ], $firstCampaignBucket, $secondCampaignBucket );

		$this->assertLogValue( [ 'test1' => 'first', 'test2' => 'second' ], 'buckets', $logfile );
	}

	/**
	 * @param mixed $expectedValue
	 * @param string $key
	 * @param resource $logfile
	 */
	private function assertLogValue( $expectedValue, $key, $logfile ) {
		rewind( $logfile );
		$logContents = fgets( $logfile );
		$this->assertNotFalse( $logContents, 'Log should contain something' );
		$event = json_decode( $logContents, true );
		$this->assertTrue( is_array( $event ) );
		$this->assertArrayHasKey( $key, $event );
		$this->assertSame( $expectedValue, $event[$key] );
	}

	// TODO testGivenMultipleWritesEachOneIsLoggedAsOneJsonLine
}
