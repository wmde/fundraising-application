<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\SofortPaymentNotification;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidSofortNotificationRequest;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortNotificationRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase
 */
class SofortPaymentNotificationUseCaseTest extends TestCase {

	public function testWhenRepositoryThrowsException_errorResponseIsReturned(): void {
		$useCase = new SofortPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
		$this->assertTrue( $response->hasErrors() );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->createMock( DonationConfirmationMailer::class );
	}

	public function testWhenNotificationIsForNonExistingDonation_unhandledResponseIsReturned(): void {
		$useCase = new SofortPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
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
			->with( $donation );

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

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->newThrowingMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newThrowingMailer() {
		$mailer = $this->getMailer();

		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );

		return $mailer;
	}

	public function testGivenSetConfirmedAtForBookedDonation_unhandledResponseIsReturned(): void {

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newCompletedSofortDonation() );

		$useCase = new SofortPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);

		$request = ValidSofortNotificationRequest::newInstantPayment();

		$response = $useCase->handleNotification( $request );
		$this->assertFalse( $response->notificationWasHandled() );
		$this->assertFalse( $response->hasErrors() );
		$this->assertSame( 'Duplicate notification', $response->getContext()['message'] );
	}

}
