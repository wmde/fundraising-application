<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\EventHandling\DomainEventHandler;

use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\DonationCreated;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\MembershipApplicationCreated;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\EventDispatcherSpy;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\PhoneNumber;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler
 */
class BucketLoggingHandlerTest extends TestCase {

	private const DONATION_ID = 23;
	private const MEMBERSHIP_ID = 42;

	public function testConstructorInstallsEventListeners(): void {
		$logger = new BucketLoggerSpy();
		$eventDispatcher = new EventDispatcherSpy();

		new BucketLoggingHandler( $logger, [ $this, 'getBuckets' ], $eventDispatcher );

		$this->assertEquals(
			[ DonationCreatedEvent::class, MembershipCreatedEvent::class ],
			$eventDispatcher->getObservedEventClassNames()
		);
	}

	public function testOnDonationCreatedHandlerLogsDonationIdAndBucket(): void {
		$logger = new BucketLoggerSpy();
		$eventDispatcher = $this->createMock( EventDispatcher::class );
		$handler = new BucketLoggingHandler( $logger, [ $this, 'getBuckets' ], $eventDispatcher );

		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, null ) );

		$this->assertSame( 1, $logger->getEventCount() );
		$this->assertInstanceOf( DonationCreated::class, $logger->getFirstEvent() );
		$this->assertSame( self::DONATION_ID, $logger->getFirstEvent()->getMetaData()['id'] );
		$this->assertCount( 1, $logger->getFirstBuckets() );
	}

	public function testOnMembershipCreatedHandlerLogsDonationIdAndBucket(): void {
		$logger = new BucketLoggerSpy();
		$eventDispatcher = $this->createMock( EventDispatcher::class );
		$handler = new BucketLoggingHandler( $logger, [ $this, 'getBuckets' ], $eventDispatcher );

		$handler->onMembershipCreated(
			new MembershipCreatedEvent( self::MEMBERSHIP_ID,
				new Applicant(
					ApplicantName::newPrivatePersonName(),
					new ApplicantAddress(),
					new EmailAddress( 'nobody@nowhere.com' ),
					new PhoneNumber( '' )
				)
			)
		);

		$this->assertSame( 1, $logger->getEventCount() );
		$this->assertInstanceOf( MembershipApplicationCreated::class, $logger->getFirstEvent() );
		$this->assertSame( self::MEMBERSHIP_ID, $logger->getFirstEvent()->getMetaData()['id'] );
		$this->assertCount( 1, $logger->getFirstBuckets() );
	}

	/**
	 * @return Bucket[]
	 */
	public function getBuckets(): array {
		$campaign = new Campaign( 'just_testing', 'gt', new CampaignDate(), new CampaignDate(), true );
		$campaign->addBucket( new Bucket( 'test1', $campaign, true ) );
		return $campaign->getBuckets();
	}
}
