<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CreditCardPaymentNotification;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\Tests\Data\ValidCreditCardNotificationRequest;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification\CreditCardNotificationUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification\CreditCardNotificationUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardNotificationUseCaseTest extends \PHPUnit_Framework_TestCase {

	/** @var DoctrineDonationRepository|FakeDonationRepository|DonationRepositorySpy */
	private $repository;
	private $authorizer;
	private $mailer;
	private $eventLogger;

	public function setUp() {
		$this->repository = new FakeDonationRepository();
		$this->authorizer = new SucceedingDonationAuthorizer();
		$this->mailer = $this->newMailer();
		$this->eventLogger = $this->newEventLogger();
	}

	public function testWhenRepositoryThrowsException_handlerReturnsFalse() {
		$this->repository = new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) );
		$this->authorizer = new FailingDonationAuthorizer();
		$useCase = $this->newCreditCardNotificationUseCase();
		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationFails_handlerReturnsFalse() {
		$this->authorizer = new FailingDonationAuthorizer();
		$this->repository->storeDonation( ValidDonation::newIncompleteCreditCardDonation() );

		$useCase = $this->newCreditCardNotificationUseCase();

		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_handlerReturnsTrue() {
		$this->repository->storeDonation( ValidDonation::newIncompleteCreditCardDonation() );

		$useCase = $this->newCreditCardNotificationUseCase();

		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentTypeIsIncorrect_handlerReturnsFalse() {
		$this->repository->storeDonation( ValidDonation::newDirectDebitDonation() );

		$useCase = $this->newCreditCardNotificationUseCase();

		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent() {
		$donation = ValidDonation::newIncompleteCreditCardDonation();
		$this->repository->storeDonation( $donation );

		$this->mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->equalTo( $donation ) );

		$useCase = $this->newCreditCardNotificationUseCase();

		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue() {
		$this->repository->storeDonation( ValidDonation::newIncompleteCreditCardDonation() );

		$this->mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );

		$useCase = $this->newCreditCardNotificationUseCase();

		$request = ValidCreditCardNotificationRequest::newBillingNotification( 1 );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}

	/**
	 * @return DonationEventLogger|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newEventLogger(): DonationEventLogger {
		return $this->createMock( DonationEventLogger::class );
	}

	private function newCreditCardNotificationUseCase() {
		return new CreditCardNotificationUseCase(
			$this->repository,
			$this->authorizer,
			$this->mailer,
			new NullLogger(),
			$this->eventLogger
		);
	}

}
