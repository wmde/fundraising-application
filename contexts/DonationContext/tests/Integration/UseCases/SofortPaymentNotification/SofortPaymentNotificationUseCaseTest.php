<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\SofortPaymentNotification;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\DonationEventLoggerAsserter;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidSofortNotificationRequest;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase
 */
class SofortPaymentNotificationUseCaseTest extends TestCase {

	use DonationEventLoggerAsserter;

	public function testWhenRepositoryThrowsException_errorResponseIsReturned(): void {
		$useCase = new SofortPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$reponse = $useCase->handleNotification( $request );
		$this->assertFalse( $reponse->notificationWasHandled() );
		$this->assertTrue( $reponse->hasErrors() );
	}

	public function testWhenNotificationIsForNonExistingDonation_unhandledResponseIsReturned(): void {
		$useCase = new SofortPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment( 4711 );

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationFails_unhandledResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompleteSofortDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_successResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompleteSofortDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_donationIsStored(): void {
		$repositorySpy = new DonationRepositorySpy( ValidDonation::newIncompleteSofortDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
	}

	public function testWhenAuthorizationSucceeds_donationIsStillPromised(): void {
		$donation = ValidDonation::newIncompleteSofortDonation();
		$repository = new FakeDonationRepository( $donation );

		$useCase = new SofortPaymentNotificationUseCase(
			$repository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertSame( Donation::STATUS_PROMISE, $repository->getDonationById( $donation->getId() )->getStatus() );
	}


	public function testWhenAuthorizationSucceeds_bookingEventIsLogged(): void {
		$donation = ValidDonation::newIncompleteSofortDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new SofortPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$eventLogger
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertEventLogContainsExpression( $eventLogger, $donation->getId(), '/booked/' );
	}

	public function testWhenPaymentTypeIsNonSofort_unhandledResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newDirectDebitDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent(): void {
		$this->markTestIncomplete( 'Do we send mail on notification or when creating the donation? Given notification means little.' );
	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompleteSofortDonation() );

		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			$this->getEventLogger()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse(): void {
		$this->markTestIncomplete( 'What do we do if donation payment has "createdAt" information already?' );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}

	/**
	 * @return DonationEventLogger|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getEventLogger(): DonationEventLogger {
		return $this->createMock( DonationEventLogger::class );
	}
}
