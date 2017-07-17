<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\SofortPaymentNotification;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidSofortNotificationRequest;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase
 */
class SofortPaymentNotificationUseCaseTest extends TestCase {

	public function testWhenRepositoryThrowsException_errorResponseIsReturned(): void {
		$useCase = new SofortPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer()
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
			$this->getMailer()
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
			$this->getMailer()
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
			$this->getMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_donationIsStored(): void {
		$repositorySpy = new DonationRepositorySpy( ValidDonation::newIncompleteSofortDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
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
			$this->getMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertSame( Donation::STATUS_PROMISE, $repository->getDonationById( $donation->getId() )->getStatus() );
	}

	public function testWhenPaymentTypeIsNonSofort_unhandledResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newDirectDebitDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent(): void {
		$donation = ValidDonation::newIncompleteSofortDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$mailer = $this->getMailer();
		$mailer
			->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->callback( function ( Donation $value ) use ( $donation ) {
				$this->assertSame( $donation->getId(), $value->getId() );
				$this->assertEquals( $donation->getDonor(), $value->getDonor() );
				$this->assertEquals( $donation->getPayment()->getAmount(), $value->getPayment()->getAmount() );
				$this->assertSame( $donation->getPayment()->getIntervalInMonths(), $value->getPayment()->getIntervalInMonths() );
				$this->assertSame( $donation->getPaymentType(), $value->getPaymentType() );
				return true;
			} ) );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer
		);

		$request = ValidSofortNotificationRequest::newInstantPayment( 1 );
		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );

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
			$mailer
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testGivenExistingTransactionIdForBookedDonation_errorResponseIsReturned(): void {

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newCompletedSofortDonation() );

		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$eventLogger
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
		$this->assertFalse( $response->hasErrors() );
		$this->assertSame( 'Duplicate notification', $response->getContext()['message'] );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}
}
