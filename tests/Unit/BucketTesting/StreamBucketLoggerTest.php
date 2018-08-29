<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\PhpTimeTeller;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\TimeTeller;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StubTimeTeller;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamBucketLogger
 */
class StreamBucketLoggerTest extends TestCase {

	/**
	 * @var resource
	 */
	private $logfile;

	/**
	 * @var TimeTeller
	 */
	private $timeTeller;

	public function setUp() {
		$this->logfile = fopen( 'php://memory', 'a+' );
		$this->timeTeller = new PhpTimeTeller();
	}

	public function testLogWriterAddsDate() {
		$stubTimeValue = 'Such a time';

		$logWriter = new StreamBucketLogger(
			$this->logfile,
			new StubTimeTeller( $stubTimeValue )
		);

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( $stubTimeValue, 'date' );
	}

	public function testGivenEventName_itIsLogged() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( 'testEventLogged', 'eventName' );
	}

	private function newBucketLogger(): BucketLogger {
		return new StreamBucketLogger( $this->logfile, $this->timeTeller );
	}

	public function testGivenEventMetadata_itIsLogged() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( (object) [ 'id' => 123, 'some_fact' => 'water_is_wet' ], 'metadata' );
	}

	public function testGivenBuckets_theyAreOutputWithTheirCampaigns() {

		$campaign1 = new Campaign( 'test1', 't1', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$firstCampaignBucket = new Bucket( 'first', $campaign1, true );
		$campaign1->addBucket( $firstCampaignBucket );
		$campaign2 = new Campaign( 'test2', 't2', new \DateTime(), (new \DateTime())->modify( '+1 month' ), true );
		$secondCampaignBucket = new Bucket( 'second', $campaign2, true );
		$campaign2->addBucket( $secondCampaignBucket );

		$this->newBucketLogger()->writeEvent(
			new FakeBucketLoggingEvent(),
			$firstCampaignBucket,
			$secondCampaignBucket
		);

		$this->assertLogValue(
			(object) [ 'test1' => 'first', 'test2' => 'second' ],
			'buckets'
		);
	}

	/**
	 * @param mixed $expectedValue
	 * @param string $key
	 */
	private function assertLogValue( $expectedValue, string $key ) {
		rewind( $this->logfile );
		$logContents = fgets( $this->logfile );
		$this->assertNotFalse( $logContents, 'Log should contain something' );
		$event = json_decode( $logContents, false );
		$this->assertTrue( is_object( $event ), 'Logs should be encoded as object' );
		$this->assertObjectHasAttribute( $key, $event, 'Event should have property' );
		$this->assertEquals( $expectedValue, $event->{$key} );
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsOneLine() {
		$logWriter = $this->newBucketLogger();

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertSame(
			3,
			substr_count( $this->getLogFileContents(), "\n" ),
			'Log should contain a newline for each event'
		);
	}

	private function getLogFileContents(): string {
		return stream_get_contents( $this->logfile, -1, 0 );
	}

	public function testGivenEventWithNewlineInMetadata_newlineIsEscaped() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent( ['text' => "line1\nline2"] ), ...[] );

		$this->assertSame(
			1,
			substr_count( $this->getLogFileContents(), "\n" ),
			'Log should contain only one newline'
		);
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsValidJsonObject() {
		$logWriter = $this->newBucketLogger();

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$logContentLines = explode( "\n", trim( $this->getLogFileContents() ) );
		foreach( $logContentLines as $line ) {
			$logData = json_decode( $line, false );
			$this->assertSame( JSON_ERROR_NONE, json_last_error(), 'JSON should be valid' );
			$this->assertInternalType( 'object', $logData );
		}
	}
}
