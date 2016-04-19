<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancellationRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;
use WMDE\Fundraising\Store\MembershipApplicationData;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelMembershipApplicationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelApplication( new CancellationRequest( 1337 ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	private function newUseCase(): CancelMembershipApplicationUseCase {
		return $this->newFactory()->newCancelMembershipApplicationUseCase( self::CORRECT_UPDATE_TOKEN );
	}

	private function newFactory(): FunFunFactory {
		$factory = TestEnvironment::newInstance()->getFactory();

		$factory->setNullMessenger();

		return $factory;
	}

	public function testResponseContainsApplicationId() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelApplication( new CancellationRequest( 1337 ) );

		$this->assertEquals( 1337, $response->getMembershipApplicationId() );
	}

	public function testGivenIdOfCancellableApplication_cancellationIsSuccessful() {
		$factory = $this->newFactory();

		$application = $this->newCancelableApplication();

		$this->storeApplication( $application, $factory );

		$useCase = $factory->newCancelMembershipApplicationUseCase( self::CORRECT_UPDATE_TOKEN );
		$response = $useCase->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
		$this->assertEquals( $application->getId(), $response->getMembershipApplicationId() );
	}

	private function newCancelableApplication(): MembershipApplication {
		return ValidMembershipApplication::newDomainEntity();
	}

	private function storeApplication( MembershipApplication $application, FunFunFactory $factory ) {
		$factory->getMembershipApplicationRepository()->storeApplication( $application );

		$doctrineApplication = $this->getDoctrineApplicationById( $factory, $application->getId() );

		$doctrineApplication->modifyDataObject( function( MembershipApplicationData $data ) {
			$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
		} );

		$factory->getEntityManager()->persist( $doctrineApplication );
		$factory->getEntityManager()->flush();
	}

	private function getDoctrineApplicationById( FunFunFactory $factory, int $id ): DoctrineApplication {
		return $factory->getEntityManager()->getRepository( DoctrineApplication::class )->find( $id );
	}

}
