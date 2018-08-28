<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamBucketLogger;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamBucketLogger
 */
class StreamBucketLoggerTest extends TestCase {

	public function testLogWriterAddsDate() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );
		$logWriter->setDateFormat( 'Y-m-d H:i' ); // Ignore seconds to avoid Heisentests

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( date( 'Y-m-d H:i' ), 'date', $logfile );
	}

	public function testGivenEventName_itIsLogged() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( 'testEventLogged', 'eventName', $logfile );
	}

	public function testGivenEventMetadata_itIsLogged() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( (object) [ 'id' => 123, 'some_fact' => 'water_is_wet' ], 'metadata', $logfile );
	}

	public function testGivenBuckets_theyAreOutputWithTheirCampaigns() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );
		$campaign1 = new Campaign( 'test1', 't1', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$firstCampaignBucket = new Bucket( 'first', $campaign1, true );
		$campaign1->addBucket( $firstCampaignBucket );
		$campaign2 = new Campaign( 'test2', 't2', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$secondCampaignBucket = new Bucket( 'second', $campaign2, true );
		$campaign2->addBucket( $secondCampaignBucket );

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), $firstCampaignBucket, $secondCampaignBucket );

		$this->assertLogValue( (object) [ 'test1' => 'first', 'test2' => 'second' ], 'buckets', $logfile );
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
		$event = json_decode( $logContents, false );
		$this->assertTrue( is_object( $event ), 'Logs should be encoded as object' );
		$this->assertObjectHasAttribute( $key, $event, 'Event should have property' );
		$this->assertEquals( $expectedValue, $event->{$key} );
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsOneLine() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$logContents = stream_get_contents( $logfile, -1, 0 );
		$this->assertSame( 3, substr_count( $logContents, "\n" ), 'Log should contain a newline for each event' );
	}

	public function testGivenEventWithNewlineInMetadata_newlineIsEscaped() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );

		$logWriter->writeEvent( new FakeBucketLoggingEvent( ['text' => "line1\nline2"] ), ...[] );

		$logContents = stream_get_contents( $logfile, -1, 0 );
		$this->assertSame( 1, substr_count( $logContents, "\n" ), 'Log should contain only one newline' );
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsValidJsonObject() {
		$logfile = fopen( 'php://memory', 'a+' );
		$logWriter = new StreamBucketLogger( $logfile );

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$logContentLines = explode( "\n", trim( stream_get_contents( $logfile, -1, 0 ) ) );
		foreach( $logContentLines as $line ) {
			$logData = json_decode( $line, false );
			$this->assertSame( JSON_ERROR_NONE, json_last_error(), 'JSON should be valid' );
			$this->assertInternalType( 'object', $logData );
		}
	}
}
