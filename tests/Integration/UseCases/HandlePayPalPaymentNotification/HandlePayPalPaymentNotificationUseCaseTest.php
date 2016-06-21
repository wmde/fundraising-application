<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Data\ValidPayPalNotificationRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationRepositorySpy;
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
			new NullLogger(),
			$this->getEventLogger()
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
			new NullLogger(),
			$this->getEventLogger()
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
			new NullLogger(),
			$this->getEventLogger()
		);

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
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
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentStatusIsPending_handlerReturnsFalse() {
		$request = ValidPayPalNotificationRequest::newPendingPayment();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
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
			$logger,
			$this->getEventLogger()
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
			new NullLogger(),
			$this->getEventLogger()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenTransactionTypeIsForSubscriptionChanges_handlerLogsStatus() {
		$logger = new LoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			$logger,
			$this->getEventLogger()
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
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceedsForAnonymousDonation_confirmationMailIsNotSent() {
		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$mailer = $this->getMailer();
		$mailer->expects( $this->never() )
			->method( 'sendConfirmationMailFor' );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_donationIsStored() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
	}

	public function testWhenAuthorizationSucceeds_donationIsBooked() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		$this->assertTrue( $donation->isBooked() );
	}

	public function testWhenAuthorizationSucceeds_bookingEventIsLogged() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$eventLogger = new DonationEventLoggerSpy();

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$eventLogger
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );

		$this->assertEventLogContainsExpression( $eventLogger, $donation->getId(), '/booked/' );
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
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testGivenNewTransactionIdForBookedDonation_transactionIdShowsUpInChildPayments() {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePaymentForDonation( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		/** @var PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$this->assertTrue( $payment->getPayPalData()->hasChildPayment( $transactionId ),
			'Parent payment must have new transaction ID in its list' );
	}

	public function testGivenNewTransactionIdForBookedDonation_childTransactionWithSameDataIsCreated() {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePaymentForDonation( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		/** @var PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$childDonation = $fakeRepository->getDonationById( $payment->getPayPalData()->getChildPaymentEntityId( $transactionId ) );
		$this->assertNotNull( $childDonation );
		/** @var PayPalPayment $childDonationPaymentMethod */
		$childDonationPaymentMethod = $childDonation->getPaymentMethod();
		$this->assertEquals( $transactionId, $childDonationPaymentMethod->getPayPalData()->getPaymentId() );
		$this->assertEquals( $donation->getAmount(), $childDonation->getAmount() );
		$this->assertEquals( $donation->getDonor(), $childDonation->getDonor() );
		$this->assertEquals( $donation->getPaymentIntervalInMonths(), $childDonation->getPaymentIntervalInMonths() );
		$this->assertTrue( $childDonation->isBooked() );
	}

	public function testGivenNewTransactionIdForBookedDonation_childCreationeventIsLogged() {
		$donation = ValidDonation::newBookedPayPalDonation();
		$transactionId = '16R12136PU8783961';

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePaymentForDonation( $donation->getId(), $transactionId );

		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$eventLogger
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );

		/** @var PayPalPayment $payment */
		$payment = $donation->getPaymentMethod();
		$childDonationId = $payment->getPayPalData()->getChildPaymentEntityId( $transactionId );

		$this->assertEventLogContainsExpression( $eventLogger, $donation->getId(), '/child donation.*' . $childDonationId .'/' );
		$this->assertEventLogContainsExpression( $eventLogger, $childDonationId, '/parent donation.*' . $donation->getId() .'/' );
	}

	public function testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newBookedPayPalDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testGivenTransactionIdInBookedChildDonation_noNewDonationIsCreated() {
		$transactionId = '16R12136PU8783961';
		$fakeChildEntityId = 2;
		$donation = ValidDonation::newBookedPayPalDonation();
		$donation->getPaymentMethod()->getPaypalData()->addChildPayment( $transactionId, $fakeChildEntityId );

		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = ValidPayPalNotificationRequest::newDuplicatePaymentForDonation( $donation->getId(), $transactionId );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenNotificationIsForNonExistingDonation_newDonationIsCreated() {
		$repositorySpy = new DonationRepositorySpy();

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 12345 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
				$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );

		$storeDonationCalls = $repositorySpy->getStoreDonationCalls();
		$this->assertCount( 1, $storeDonationCalls, 'Donation is stored' );
		$this->assertNull( $storeDonationCalls[0]->getId(), 'ID is not taken from request' );
		$this->assertDonationIsCreatedWithNotficationRequestData( $storeDonationCalls[0] );
	}

	public function testGivenRecurringPaymentForBookedDonation_newDonationIsCreated() {
		$donation = ValidDonation::newBookedPayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newRecurringPayment( $donation->getId() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );

		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
		/** @var Donation $newDonation */
		$newDonation = $repositorySpy->getStoreDonationCalls()[0];
		$this->assertNotEquals( $donation, $newDonation );

		$this->assertDonationIsCreatedWithNotficationRequestData( $newDonation );
	}

	public function testGivenRecurringPaymentForIncompleteDonation_donationIsBooked() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newRecurringPayment( $donation->getId() );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );

		$this->assertCount( 1, $repositorySpy->getStoreDonationCalls() );
		$this->assertEquals( $donation, $repositorySpy->getStoreDonationCalls()[0] );
		$this->assertTrue( $donation->isBooked() );
	}

	public function testWhenNotificationIsForNonExistingDonation_confirmationMailIsSent() {
		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 12345 );
		$mailer = $this->getMailer();
		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->anything() );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$mailer,
			new NullLogger(),
			$this->getEventLogger()
		);

		$useCase->handleNotification( $request );
	}

	public function testWhenNotificationIsForNonExistingDonation_bookingEventIsLogged() {
		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 12345 );
		$eventLogger = new DonationEventLoggerSpy();

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new FakeDonationRepository(),
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger(),
			$eventLogger
		);

		$useCase->handleNotification( $request );

		$this->assertEventLogContainsExpression( $eventLogger, 1, '/booked/' ); // 1 is the generated donation id
	}

	private function assertDonationIsCreatedWithNotficationRequestData( Donation $donation ) {
		$this->assertSame( 0, $donation->getPaymentIntervalInMonths(), 'Payment interval is always empty' );
		$this->assertTrue( $donation->isBooked() );

		$donorName = $donation->getDonor()->getPersonName();
		$this->assertSame( PersonName::PERSON_PRIVATE, $donorName->getPersonType(), 'Person is always private' );
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

	private function assertEventLogContainsExpression( DonationEventLoggerSpy $eventLoggerSpy, int $donationId, string $expr ) {
		$foundCalls = array_filter( $eventLoggerSpy->getLogCalls(), function( $call ) use ( $donationId, $expr ) {
			return $call[0] == $donationId && preg_match( $expr, $call[1] );
		} );
		$assertMsg = 'Failed to assert that donation event log log contained "' . $expr . '" for donation id '.$donationId;
		$this->assertCount( 1, $foundCalls, $assertMsg );
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
