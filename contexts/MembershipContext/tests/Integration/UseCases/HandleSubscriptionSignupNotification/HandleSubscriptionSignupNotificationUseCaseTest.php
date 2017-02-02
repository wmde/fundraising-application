<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\UseCases\HandleSubscriptionPaymentNotification;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\FailingAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\FakeApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\SucceedingAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionSignupNotification\HandleSubscriptionSignupNotificationUseCase;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\PaymentContext\Tests\Data\ValidSubscriptionSignupRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionSignupNotification\HandleSubscriptionSignupNotificationUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandleSubscriptionSignupNotificationUseCaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailerSpy;

	public function setUp() {
		$this->mailerSpy = new TemplateBasedMailerSpy( $this );
	}

	public function testWhenPaymentMethodIsNotPayPal_requestIsNotHandled() {
		$fakeRepository = new FakeApplicationRepository();
		$fakeRepository->storeApplication( ValidMembershipApplication::newDomainEntity() );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new SucceedingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
	}

	public function testWhenMembershipApplicationDoesNotExist_requestIsNotHandled() {
		$fakeRepository = new FakeApplicationRepository();
		$fakeRepository->storeApplication( ValidMembershipApplication::newDomainEntity() );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new SucceedingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$request->setApplicationId( 667 );
		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
	}

	public function testWhenRepositoryThrowsException_responseContainsErrors() {
		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			new DoctrineApplicationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new SucceedingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);
		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
		$this->assertTrue( $response->hasErrors() );
	}

	public function testWhenAuthorizationFails_requestIsNotHandled() {
		$fakeRepository = new FakeApplicationRepository();
		$fakeRepository->storeApplication( ValidMembershipApplication::newDomainEntityUsingPayPal() );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new FailingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
	}

	public function testWhenStatusIsNotAllowedForConfirming_requestIsNotHandled() {
		$fakeRepository = new FakeApplicationRepository();

		$application = ValidMembershipApplication::newDomainEntityUsingPayPal();
		$application->confirm();
		$fakeRepository->storeApplication( $application );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new SucceedingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
		$this->assertTrue( $response->hasErrors() );
	}

	public function testWhenValidRequestIsSent_itIsHandled() {
		$fakeRepository = new FakeApplicationRepository();

		$application = ValidMembershipApplication::newDomainEntityUsingPayPal();
		$fakeRepository->storeApplication( $application );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new SucceedingAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$response = $useCase->handleNotification( $request );
		$this->assertTrue( $response->notificationWasHandled() );
		$this->assertFalse( $response->hasErrors() );
	}

	public function testWhenApplicationIsConfirmed_mailIsSent() {
		$fakeRepository = new FakeApplicationRepository();

		$application = ValidMembershipApplication::newDomainEntityUsingPayPal();
		$fakeRepository->storeApplication( $application );

		$useCase = new HandleSubscriptionSignupNotificationUseCase(
			$fakeRepository,
			new SucceedingAuthorizer(),
			$this->mailerSpy,
			new NullLogger()
		);

		$request = ValidSubscriptionSignupRequest::newValidRequest();
		$useCase->handleNotification( $request );
		$this->mailerSpy->assertCalledOnce();
	}

	/**
	 * @return TemplateBasedMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): TemplateBasedMailer {
		return $this->getMockBuilder( TemplateBasedMailer::class )->disableOriginalConstructor()->getMock();
	}

}
