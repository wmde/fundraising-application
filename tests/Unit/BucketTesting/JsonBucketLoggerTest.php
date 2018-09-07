<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\JsonBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\TimeTeller;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LogWriterSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StubTimeTeller;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\JsonBucketLogger
 */
class JsonBucketLoggerTest extends TestCase {

	private const STUB_TIME_VALUE = 'Such a time';

	/**
	 * @var LogWriterSpy
	 */
	private $logWriter;

	/**
	 * @var TimeTeller
	 */
	private $timeTeller;

	public function setUp() {
		$this->logWriter = new LogWriterSpy();
		$this->timeTeller = new StubTimeTeller( self::STUB_TIME_VALUE );
	}

	public function testLogWriterAddsDate() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( self::STUB_TIME_VALUE, 'date' );
	}

	public function testGivenEventName_itIsLogged() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( 'testEventLogged', 'eventName' );
	}

	private function newBucketLogger(): BucketLogger {
		return new JsonBucketLogger( $this->logWriter, $this->timeTeller );
	}

	public function testGivenEventMetadata_itIsLogged() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertLogValue( (object) [ 'id' => 123, 'some_fact' => 'water_is_wet' ], 'metadata' );
	}

	public function testGivenBuckets_theyAreOutputWithTheirCampaigns() {
		$this->newBucketLogger()->writeEvent(
			new FakeBucketLoggingEvent(),
			$this->newBucket( 'first', 'test1' ),
			$this->newBucket( 'second', 'test2' )
		);

		$this->assertLogValue(
			(object) [ 'test1' => 'first', 'test2' => 'second' ],
			'buckets'
		);
	}

	private function newBucket( string $bucketName, string $campaignName ): Bucket {
		return new Bucket(
			$bucketName,
			new Campaign(
				$campaignName,
				$bucketName . $campaignName,
				new \DateTime(),
				( new \DateTime() )->modify( '+1 month' ),
				true
			),
			true
		);
	}

	/**
	 * @param mixed $expectedValue
	 * @param string $key
	 */
	private function assertLogValue( $expectedValue, string $key ) {
		$logCalls = $this->logWriter->getWriteCalls();

		$this->assertNotEmpty( $logCalls, 'Log should contain something' );

		$event = json_decode( $logCalls[0], false );
		$this->assertTrue( is_object( $event ), 'Logs should be encoded as object' );
		$this->assertObjectHasAttribute( $key, $event, 'Event should have property' );
		$this->assertEquals( $expectedValue, $event->{$key} );
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsOneLine() {
		$logWriter = $this->newBucketLogger();

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		$this->assertCount(
			3,
			$this->logWriter->getWriteCalls(),
			'Log should contain an entry for each event'
		);
	}

	public function testGivenEventWithNewlineInMetadata_newlineIsEscaped() {
		$this->newBucketLogger()->writeEvent( new FakeBucketLoggingEvent( [ 'text' => "line1\nline2" ] ), ...[] );

		$this->assertSame(
			0,
			substr_count( $this->logWriter->getWriteCalls()[0], "\n" ),
			'Logger should escape newlines'
		);
	}

	public function testGivenMultipleEvents_eachOneIsLoggedAsValidJsonObject() {
		$logWriter = $this->newBucketLogger();

		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );
		$logWriter->writeEvent( new FakeBucketLoggingEvent(), ...[] );

		foreach( $this->logWriter->getWriteCalls() as $line ) {
			$logData = json_decode( $line, false );
			$this->assertSame( JSON_ERROR_NONE, json_last_error(), 'JSON should be valid' );
			$this->assertInternalType( 'object', $logData );
		}
	}

}
