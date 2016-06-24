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

	/**
	 * @dataProvider validTrackingDataProvider
	 */
	public function testValidTrackingDataIsProperlyApplied( string $campaignCode, string $keyword ) {
		$application = ValidMembershipApplication::newDoctrineEntity();
		$this->persistApplication( $application );

		$this->newMembershipApplicationTracker()->trackApplication(
			$application->getId(),
			$this->newMembershipApplicationTrackingInfo( $campaignCode, $keyword )
		);

		$storedApplication = $this->getApplicationById( $application->getId() );

		$this->assertSame( $keyword, $storedApplication->getDecodedData()['confirmationPage'] );
		$this->assertSame( $campaignCode, $storedApplication->getDecodedData()['confirmationPageCampaign'] );
	}

	public function validTrackingDataProvider() {
		return [
			[ 'campaignCode', 'keyword' ],
			[ '', 'keyword', 'keyword' ],
			[ 'campaignCode', '' ],
			[ '', '' ],
		];
	}

	private function persistApplication( MembershipApplication $application ) {
		$this->entityManager->persist( $application );
		$this->entityManager->flush();
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		return $this->entityManager->find( MembershipApplication::class, $applicationId );
	}

	private function newMembershipApplicationTracker(): MembershipApplicationTracker {
		return new DoctrineMembershipApplicationTracker( $this->entityManager );
	}

	private function newMembershipApplicationTrackingInfo( $campaignCode, $keyword ) {
		return new MembershipApplicationTrackingInfo( $campaignCode, $keyword );
	}

}
