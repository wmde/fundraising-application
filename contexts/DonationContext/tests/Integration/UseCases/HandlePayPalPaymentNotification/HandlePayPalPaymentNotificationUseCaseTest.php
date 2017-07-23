<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidPayPalNotificationRequest;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\DonationEventLoggerAsserter;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCaseTest extends TestCase {

	use DonationEventLoggerAsserter;

	public function testWhenRepositoryThrowsException_errorResponseIsReturned(): void {
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);
		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$reponse = $useCase->handleNotification( $request );
		$this->assertFalse( $reponse->notificationWasHandled() );
		$this->assertTrue( $reponse->hasErrors() );
	}

	public function testWhenAuthorizationFails_unhandledResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_successResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenPaymentTypeIsNonPayPal_unhandledResponseIsReturned(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newDirectDebitDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenPaymentStatusIsPending_unhandledResponseIsReturned(): void {
		$request = ValidPayPalNotificationRequest::newPendingPayment();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenTransactionTypeIsForSubscriptionChanges_unhandledResponseIsReturned(): void {
		$request = ValidPayPalNotificationRequest::newSubscriptionModification();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);
		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $donation );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			$this->getEventLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceedsForAnonymousDonation_confirmationMailIsNotSent(): void {
		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$mailer = $this->getMailer();
		$mailer->expects( $this->never() )
			->method( 'sendConfirmationMailFor' );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenAuthorizationSucceeds_donationIsStored(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
	}

	public function testWhenAuthorizationSucceeds_donationIsBooked(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repository = new FakeDonationRepository( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
		$this->assertTrue( $repository->getDonationById( $donation->getId() )->isBooked() );
	}

	public function testWhenAuthorizationSucceeds_bookingEventIsLogged(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$eventLogger = new DonationEventLoggerSpy();

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$eventLogger
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );

		$this->assertEventLogContainsExpression( $eventLogger, $donation->getId(), '/booked/' );
	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testGivenNewTransactionIdForBookedDonation_transactionIdShowsUpInChildPayments(): void {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePayment( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );

		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $payment */
		$payment = $fakeRepository->getDonationById( $donation->getId() )->getPaymentMethod();

		$this->assertTrue(
			$payment->getPayPalData()->hasChildPayment( $transactionId ),
			'Parent payment must have new transaction ID in its list'
		);
	}

	public function testGivenNewTransactionIdForBookedDonation_childTransactionWithSameDataIsCreated(): void {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePayment( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );

		$donation = $fakeRepository->getDonationById( $donation->getId() );
		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$childDonation = $fakeRepository->getDonationById( $payment->getPayPalData()->getChildPaymentEntityId( $transactionId ) );
		$this->assertNotNull( $childDonation );
		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $childDonationPaymentMethod */
		$childDonationPaymentMethod = $childDonation->getPaymentMethod();
		$this->assertEquals( $transactionId, $childDonationPaymentMethod->getPayPalData()->getPaymentId() );
		$this->assertEquals( $donation->getAmount(), $childDonation->getAmount() );
		$this->assertEquals( $donation->getDonor(), $childDonation->getDonor() );
		$this->assertEquals( $donation->getPaymentIntervalInMonths(), $childDonation->getPaymentIntervalInMonths() );
		$this->assertTrue( $childDonation->isBooked() );
	}

	public function testGivenNewTransactionIdForBookedDonation_childCreationeventIsLogged(): void {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePayment( $donation->getId(), $transactionId );

		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$eventLogger
		);

		$this->assertTrue( $useCase->handleNotification( $request )->notificationWasHandled() );

		$donation = $fakeRepository->getDonationById( $donation->getId() );
		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$childDonationId = $payment->getPayPalData()->getChildPaymentEntityId( $transactionId );

		$this->assertEventLogContainsExpression( $eventLogger, $donation->getId(), '/child donation.*' . $childDonationId .'/' );
		$this->assertEventLogContainsExpression( $eventLogger, $childDonationId, '/parent donation.*' . $donation->getId() .'/' );
	}

	public function testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse(): void {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newBookedPayPalDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPayment( 1 );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testGivenTransactionIdInBookedChildDonation_noNewDonationIsCreated(): void {
		$transactionId = '16R12136PU8783961';
		$fakeChildEntityId = 2;
		$donation = ValidDonation::newBookedPayPalDonation();
		$donation->getPaymentMethod()->getPaypalData()->addChildPayment( $transactionId, $fakeChildEntityId );

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePayment( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request )->notificationWasHandled() );
	}

	public function testWhenNotificationIsForNonExistingDonation_newDonationIsCreated(): void {
		$repositorySpy = new DonationRepositorySpy();

		$request = ValidPayPalNotificationRequest::newInstantPayment( 12345 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );

		$storeDonationCalls = $repositorySpy->getStoreDonationCalls();
		$this->assertCount( 1, $storeDonationCalls, 'Donation is stored' );
		$this->assertNull( $storeDonationCalls[0]->getId(), 'ID is not taken from request' );
		$this->assertDonationIsCreatedWithNotficationRequestData( $storeDonationCalls[0] );
	}

	public function testGivenRecurringPaymentForIncompleteDonation_donationIsBooked(): void {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newRecurringPayment( $donation->getId() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );
		$donation = $repositorySpy->getDonationById( $donation->getId() );

		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
		$this->assertEquals( $donation, $repositorySpy->getStoreDonationCalls()[0] );
		$this->assertTrue( $donation->isBooked() );
	}

	public function testWhenNotificationIsForNonExistingDonation_confirmationMailIsSent(): void {
		$request = ValidPayPalNotificationRequest::newInstantPayment( 12345 );
		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->anything() );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$mailer,
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );
	}

	public function testWhenNotificationIsForNonExistingDonation_bookingEventIsLogged(): void {
		$request = ValidPayPalNotificationRequest::newInstantPayment( 12345 );
		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$eventLogger
		);

		$useCase->handleNotification( $request );

		$this->assertEventLogContainsExpression( $eventLogger, 1, '/booked/' ); // 1 is the generated donation id
	}

	private function assertDonationIsCreatedWithNotficationRequestData( Donation $donation ): void {
		$this->assertSame( 0, $donation->getPaymentIntervalInMonths(), 'Payment interval is always empty' );
		$this->assertTrue( $donation->isBooked() );

		$donorName = $donation->getDonor()->getName();
		$this->assertSame( DonorName::PERSON_PRIVATE, $donorName->getPersonType(), 'Person is always private' );
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_NAME, $donorName->getFullName() );

		$this->assertSame( ValidPayPalNotificationRequest::PAYER_EMAIL, $donation->getDonor()->getEmailAddress() );

		$address = $donation->getDonor()->getPhysicalAddress();
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_STREET, $address->getStreetAddress() );
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_CITY, $address->getCity() );
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_POSTAL_CODE, $address->getPostalCode() );
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_COUNTRY_CODE, $address->getCountryCode() );

		$payment = $donation->getPayment();
		$this->assertSame( ValidPayPalNotificationRequest::AMOUNT_GROSS_CENTS, $payment->getAmount()->getEuroCents() );

		/** @var PayPalData $paypalData */
		$paypalData = $payment->getPaymentMethod()->getPaypalData();
		$this->assertSame( ValidPayPalNotificationRequest::PAYER_ADDRESS_NAME, $paypalData->getAddressName() );
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
