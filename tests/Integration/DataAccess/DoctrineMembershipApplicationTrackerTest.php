<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationTracker;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTracker;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationTracker
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DoctrineMembershipApplicationTrackerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function validTrackingDataProvider() {
		return [
			[ 'campaignCode', 'keyword', 'campaignCode/keyword' ],
			[ '', 'keyword', 'keyword' ],
			[ 'campaignCode', '', '' ],
			[ '', '', '' ],
		];
	}

	/** @dataProvider validTrackingDataProvider */
	public function testValidTrackingDataIsProperlyApplied( string $campaignCode, string $keyword, $expectedTracking ) {
		#$repository = $this->newRepository();
		$application = ValidMembershipApplication::newDoctrineEntity();
		$this->persistApplication( $application );

		$this->newMembershipApplicationTracker()->trackApplication(
			$application->getId(),
			$this->newMembershipApplicationTrackingInfo( $campaignCode, $keyword )
		);

		$this->assertApplicationTrackedProperly( $application->getId(), $expectedTracking );
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		return $this->entityManager->find( MembershipApplication::class, $applicationId );
	}

	private function persistApplication( MembershipApplication $application ) {
		$this->entityManager->persist( $application );
		$this->entityManager->flush();
	}

	private function newMembershipApplicationTracker(): MembershipApplicationTracker {
		return new DoctrineMembershipApplicationTracker( $this->entityManager );
	}

	private function newMembershipApplicationTrackingInfo( $campaignCode, $keyword ) {
		return new MembershipApplicationTrackingInfo( $campaignCode, $keyword );
	}

	private function assertApplicationTrackedProperly( int $applicationId, $expectedTrackingData ) {
		$application = $this->getApplicationById( $applicationId );

		$this->assertSame( $expectedTrackingData, $application->getTracking() );
	}

}
