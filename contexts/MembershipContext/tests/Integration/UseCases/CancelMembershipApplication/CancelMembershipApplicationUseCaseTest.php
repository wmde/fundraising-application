<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\FailingAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\InMemoryApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\SucceedingAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\CancelMembershipApplication\CancellationRequest;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipContext\UseCases\CancelMembershipApplication\CancelMembershipApplicationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelMembershipApplicationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;

	/**
	 * @var ApplicationAuthorizer
	 */
	private $authorizer;

	/**
	 * @var ApplicationRepository
	 */
	private $repository;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	public function setUp() {
		$this->authorizer = new SucceedingAuthorizer();
		$this->repository = new InMemoryApplicationRepository();
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

	private function newCancelableApplication(): Application {
		return ValidMembershipApplication::newDomainEntity();
	}

	private function storeApplication( Application $application ) {
		$this->repository->storeApplication( $application );
	}

	public function testWhenApplicationGetsCancelled_cancellationConfirmationEmailIsSend() {
		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->mailer->assertCalledOnceWith(
			$application->getApplicant()->getEmailAddress(),
			[
				'membershipApplicant' => [
					'salutation' => $application->getApplicant()->getName()->getSalutation(),
					'title' => $application->getApplicant()->getName()->getTitle(),
					'lastName' => $application->getApplicant()->getName()->getLastName()
				],
			'applicationId' => 1
			]
		);
	}

	public function testNotAuthorized_cancellationFails() {
		$this->authorizer = new FailingAuthorizer();

		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$response = $this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	public function testNotAuthorized_cancellationSucceeds() {
		$this->authorizer = new SucceedingAuthorizer();

		$application = $this->newCancelableApplication();
		$this->storeApplication( $application );

		$response = $this->newUseCase()->cancelApplication( new CancellationRequest( $application->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

}
