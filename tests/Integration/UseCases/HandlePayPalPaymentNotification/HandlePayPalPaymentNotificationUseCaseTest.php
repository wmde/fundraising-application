<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Data\ValidPayPalNotificationRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testWhenRepositoryThrowsException_handlerReturnsFalse() {
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);
		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationFails_handlerReturnsFalse() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_handlerReturnsTrue() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenDonationIsNotFound_handlerCreatesOneAndReturnsTrue() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 12345 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentTypeIsNonPayPal_handlerReturnsFalse() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newDirectDebitDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentStatusIsPending_handlerReturnsFalse() {
		$request = ValidPayPalNotificationRequest::newPendingPayment();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentStatusIsPending_handlerLogsStatus() {
		$logger = new LoggerSpy();

		$request = $request = ValidPayPalNotificationRequest::newPendingPayment();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$logger
		);

		$useCase->handleNotification( $request );

		$logger->assertCalledOnceWithMessage( 'Unhandled PayPal notification: Pending', $this );
	}

	public function testWhenTransactionTypeIsForSubscriptionChanges_handlerReturnsFalse() {
		$request = ValidPayPalNotificationRequest::newSubscriptionModification();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenTransactionTypeIsForSubscriptionChanges_handlerLogsStatus() {
		$logger = new LoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$logger
		);

		$request = ValidPayPalNotificationRequest::newSubscriptionModification();

		$useCase->handleNotification( $request );

		$logger->assertCalledOnceWithMessage( 'Unhandled PayPal subscription notification: subscr_modify', $this );
	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->equalTo( $donation ) );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testGivenNewTransactionIdForBookedDonation_transactionIdShowsUpInChildPayments() {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( $donation->getId() );
		$request->setTransactionId( $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		/** @var PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$this->assertTrue( $payment->getPayPalData()->hasChildPayment( $transactionId ),
			'Parent payment must have new transaction ID in its list' );
	}

	// TODO testGivenNewTransactionIdForBookedDonation_childTransactionIsCreated
	// TODO testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse
	// TODO testGivenTransactionIdInBookedChildDonation_noNewDonationIsCreated

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}

}
