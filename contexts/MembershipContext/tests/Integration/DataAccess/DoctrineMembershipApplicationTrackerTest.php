<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\MembershipContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\MembershipContext\DataAccess\DoctrineApplicationTracker;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\MembershipContext\Tracking\ApplicationTracker;
use WMDE\Fundraising\Frontend\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipContext\DataAccess\DoctrineApplicationTracker
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

	private function newMembershipApplicationTracker(): ApplicationTracker {
		return new DoctrineApplicationTracker( $this->entityManager );
	}

	private function newMembershipApplicationTrackingInfo( $campaignCode, $keyword ) {
		return new MembershipApplicationTrackingInfo( $campaignCode, $keyword );
	}

}
