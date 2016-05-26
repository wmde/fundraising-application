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
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Data\ValidPayPalNotificationRequest;
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

	public function testWhenAuthorizationSucceeds_donationIsStored() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$repositorySpy = new DonationRepositorySpy( $donation );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$repositorySpy,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
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
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
		$this->assertTrue( $donation->isBooked() );
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

		$request = ValidPayPalNotificationRequest::newDuplicatePaymentForDonation( $donation->getId(), $transactionId );

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
		// TODO Check donation log for new entry, see https://phabricator.wikimedia.org/T135522
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
			new NullLogger()
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
		// TODO Check donation log for new entry, see https://phabricator.wikimedia.org/T135522
	}

	public function testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newBookedPayPalDonation() );

		$request = ValidPayPalNotificationRequest::newInstantPaymentForDonation( 1 );

		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
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
			new NullLogger()
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
			new NullLogger()
		);

		$useCase->handleNotification( $request );

		$storeDonationCalls = $repositorySpy->getStoreDonationCalls();
		$this->assertCount( 1, $storeDonationCalls, 'Donation is stored' );
		$this->assertNull( $storeDonationCalls[0]->getId(), 'ID is not taken from request' );
		$this->assertDonationIsCreatedWithNotficationRequestData( $storeDonationCalls[0] );
	}

	private function assertDonationIsCreatedWithNotficationRequestData( Donation $donation ) {
		$this->assertEquals( 0, $donation->getPaymentIntervalInMonths(), 'Payment interval is always empty' );
		$this->assertTrue( $donation->isBooked() );

		$donorName = $donation->getDonor()->getPersonName();
		$this->assertEquals( PersonName::PERSON_PRIVATE, $donorName->getPersonType(), 'Person is always private' );
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_NAME, $donorName->getFullName() );

		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_EMAIL, $donation->getDonor()->getEmailAddress() );

		$address = $donation->getDonor()->getPhysicalAddress();
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_STREET, $address->getStreetAddress() );
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_CITY, $address->getCity() );
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_POSTAL_CODE, $address->getPostalCode() );
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_COUNTRY_CODE, $address->getCountryCode() );

		$payment = $donation->getPayment();
		$this->assertEquals( ValidPayPalNotificationRequest::AMOUNT_GROSS_CENTS, $payment->getAmount()->getEuroCents() );

		/** @var PayPalData $paypalData */
		$paypalData = $payment->getPaymentMethod()->getPaypalData();
		$this->assertEquals( ValidPayPalNotificationRequest::PAYER_ADDRESS_NAME, $paypalData->getAddressName() );
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
			new NullLogger()
		);

		$useCase->handleNotification( $request );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}

}
