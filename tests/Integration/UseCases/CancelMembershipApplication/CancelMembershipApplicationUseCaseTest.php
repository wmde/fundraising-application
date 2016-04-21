<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingMembershipAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingMembershipAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancellationRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelMembershipApplicationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;

	/**
	 * @var MembershipApplicationAuthorizer
	 */
	private $authorizer;

	/**
	 * @var MembershipApplicationRepository
	 */
	private $repository;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	public function setUp() {
		$this->authorizer = new SucceedingMembershipAuthorizer();
		$this->repository = new InMemoryMembershipApplicationRepository();
		$this->mailer = new TemplateBasedMailerSpy( $this );
	}

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelApplication( new CancellationRequest( self::ID_OF_NON_EXISTING_APPLICATION ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	private function newUseCase(): CancelMembershipApplicationUseCase {
		return new CancelMembershipApplicationUseCase(
			$this->authorizer,
			$this->repository,
			$this->mailer
		);
	}

	public function testFailureResponseContainsApplicationId() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelApplication( new CancellationRequest( self::ID_OF_NON_EXISTING_APPLICATION ) );

		$this->assertEquals( self::ID_OF_NON_EXISTING_APPLICATION, $response->getMembershipApplicationId() );
	}

	public function testGivenIdOfCancellableApplication_cancellationIsSuccessful() {
		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$response = $this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
		$this->assertEquals( $application->getId(), $response->getMembershipApplicationId() );
	}

	private function newCancelableApplication(): MembershipApplication {
		return ValidMembershipApplication::newDomainEntity();
	}

	private function storeApplication( MembershipApplication $application ) {
		$this->repository->storeApplication( $application );
	}

	public function testWhenApplicationGetsCancelled_cancellationConfirmationEmailIsSend() {
		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->mailer->assertMailerCalledOnceWith(
			$application->getApplicant()->getEmailAddress(),
			[
				'salutation' => 'Sehr geehrte Damen und Herren,',
				'applicationId' => 1
			]
		);
	}

	public function testNotAuthorized_cancellationFails() {
		$this->authorizer = new FailingMembershipAuthorizer();

		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$response = $this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	public function testNotAuthorized_cancellationSucceeds() {
		$this->authorizer = new SucceedingMembershipAuthorizer();

		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$response = $this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

}
