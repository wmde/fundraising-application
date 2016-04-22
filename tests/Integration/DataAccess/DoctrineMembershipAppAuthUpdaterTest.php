<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipAppAuthUpdater
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipAppAuthUpdaterTest extends \PHPUnit_Framework_TestCase {

	const ACCESS_TOKEN = 'kindly allow me access';
	const UPDATE_TOKEN = 'kindly accept my datas';

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testWhenApplicationExists_updateTokenGetSet() {
		$application = ValidMembershipApplication::newDoctrineEntity();

		$this->persistApplication( $application );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			$application->getId(),
			self::UPDATE_TOKEN
		);

		$this->assertApplicationHasUpdateToken(
			$application->getId(),
			self::UPDATE_TOKEN
		);
	}

	private function newAuthorizationUpdater(): MembershipAppAuthUpdater {
		return new DoctrineMembershipAppAuthUpdater( $this->entityManager );
	}

	private function persistApplication( MembershipApplication $application ) {
		$this->entityManager->persist( $application );
		$this->entityManager->flush();
	}

	private function assertApplicationHasUpdateToken( int $applicationId, string $token ) {
		$application = $this->getApplicationById( $applicationId );

		$this->assertSame( $token, $application->getDataObject()->getUpdateToken() );
	}

	private function getApplicationById( int $applicationId ): MembershipApplication {
		return $this->entityManager->find( MembershipApplication::class, $applicationId );
	}

	public function testWhenApplicationDoesNotExist_assigningUpdateTokenCausesException() {
		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			1337,
			self::UPDATE_TOKEN
		);
	}

	public function testWhenApplicationDoesNotExist_assigningAccessTokenCausesException() {
		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowAccessViaToken(
			1337,
			self::ACCESS_TOKEN
		);
	}

	public function testWhenApplicationExists_accessTokenGetsSet() {
		$application = ValidMembershipApplication::newDoctrineEntity();

		$this->persistApplication( $application );

		$this->newAuthorizationUpdater()->allowAccessViaToken(
			$application->getId(),
			self::ACCESS_TOKEN
		);

		$application = $this->getApplicationById( $application->getId() );
		$this->assertSame( self::ACCESS_TOKEN, $application->getDataObject()->getAccessToken() );
	}

}
