<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Tests\Integration\DataAccess;

use Codeception\Specify;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Store\MembershipApplicationData;

/**
 * @covers \WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationAuthorizer
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationAuthorizerTest extends \PHPUnit\Framework\TestCase {
	use Specify;

	const CORRECT_UPDATE_TOKEN = 'CorrectUpdateToken';
	const CORRECT_ACCESS_TOKEN = 'CorrectAccessToken';
	const WRONG__UPDATE_TOKEN = 'WrongUpdateToken';
	const WRONG_ACCESS_TOKEN = 'WrongAccessToken';
	const MEANINGLESS_APPLICATION_ID = 1337;
	const ID_OF_WRONG_APPLICATION = 42;

	private function newAuthorizerWithApplications( string $updateToken = null,
		string $accessToken = null, MembershipApplication ...$applications ): ApplicationAuthorizer {

		$entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();

		foreach ( $applications as $application ) {
			$entityManager->persist( $application );
		}

		$entityManager->flush();

		return new DoctrineApplicationAuthorizer( $entityManager, $updateToken, $accessToken );
	}

	/**
	 * @slowThreshold 400
	 */
	public function testWhenNoMembershipApplications(): void {
		$this->specify( 'update authorization fails', function(): void {
			$authorizer = $this->newAuthorizerWithApplications( self::CORRECT_UPDATE_TOKEN );
			$this->assertFalse( $authorizer->canModifyApplication( self::MEANINGLESS_APPLICATION_ID ) );
		} );

		$this->specify( 'access authorization fails', function(): void {
			$authorizer = $this->newAuthorizerWithApplications( self::CORRECT_ACCESS_TOKEN );
			$this->assertFalse( $authorizer->canAccessApplication( self::MEANINGLESS_APPLICATION_ID ) );
		} );
	}

	/**
	 * @slowThreshold 1200
	 */
	public function testWhenApplicationWithTokenExists(): void {
		$application = new MembershipApplication();

		$application->modifyDataObject( function( MembershipApplicationData $data ): void {
			$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
			$data->setAccessToken( self::CORRECT_ACCESS_TOKEN );
		} );

		$this->specify(
			'given correct application id and correct token, update authorization succeeds',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( self::CORRECT_UPDATE_TOKEN, null, $application );
				$this->assertTrue( $authorizer->canModifyApplication( $application->getId() ) );
			}
		);

		$this->specify(
			'given wrong application id and correct token, update authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( self::CORRECT_UPDATE_TOKEN, null, $application );
				$this->assertFalse( $authorizer->canModifyApplication( self::ID_OF_WRONG_APPLICATION ) );
			}
		);

		$this->specify(
			'given correct application id and wrong token, update authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( self::WRONG__UPDATE_TOKEN, null, $application );
				$this->assertFalse( $authorizer->canModifyApplication( $application->getId() ) );
			}
		);

		$this->specify(
			'given correct application id and correct token, access authorization succeeds',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( null, self::CORRECT_ACCESS_TOKEN, $application );
				$this->assertTrue( $authorizer->canAccessApplication( $application->getId() ) );
			}
		);

		$this->specify(
			'given wrong application id and correct token, access authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( null, self::CORRECT_ACCESS_TOKEN, $application );
				$this->assertFalse( $authorizer->canAccessApplication( self::ID_OF_WRONG_APPLICATION ) );
			}
		);

		$this->specify(
			'given correct application id and wrong token, access authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( null, self::WRONG_ACCESS_TOKEN, $application );
				$this->assertFalse( $authorizer->canAccessApplication( $application->getId() ) );
			}
		);
	}

	/**
	 * @slowThreshold 400
	 */
	public function testWhenApplicationWithoutTokenExists(): void {
		$application = new MembershipApplication();

		$this->specify(
			'given correct application id and a token, update authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( 'SomeToken', null, $application );
				$this->assertFalse( $authorizer->canModifyApplication( $application->getId() ) );
			}
		);

		$this->specify(
			'given correct application id and a token, access authorization fails',
			function() use ( $application ): void {
				$authorizer = $this->newAuthorizerWithApplications( null, 'SomeToken', $application );
				$this->assertFalse( $authorizer->canAccessApplication( $application->getId() ) );
			}
		);
	}

	/**
	 * @slowThreshold 400
	 */
	public function testWhenDoctrineThrowsException(): void {
		$authorizer = new DoctrineApplicationAuthorizer(
			$this->getThrowingEntityManager(),
			self::CORRECT_UPDATE_TOKEN,
			self::CORRECT_ACCESS_TOKEN
		);

		$this->specify( 'update authorization fails', function() use ( $authorizer ): void {
			$this->assertFalse( $authorizer->canModifyApplication( self::MEANINGLESS_APPLICATION_ID ) );
		} );

		$this->specify( 'access authorization fails', function() use ( $authorizer ): void {
			$this->assertFalse( $authorizer->canAccessApplication( self::MEANINGLESS_APPLICATION_ID ) );
		} );
	}

	private function getThrowingEntityManager(): EntityManager {
		$entityManager = $this->getMockBuilder( EntityManager::class )
			->disableOriginalConstructor()->getMock();

		$entityManager->method( $this->anything() )
			->willThrowException( new ORMException() );

		return $entityManager;
	}

}
