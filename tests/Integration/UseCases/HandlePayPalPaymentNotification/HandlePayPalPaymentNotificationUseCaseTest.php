<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\PayPalNotificationRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\HandlePayPalPaymentNotificationUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testWhenRepositoryThrowsException_handlerReturnsFalse() {
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);
		$this->assertFalse( $useCase->handleNotification( $this->newRequest( 1 ) ) );
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

		$this->assertFalse( $useCase->handleNotification( $this->newRequest( 1 ) ) );
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

		$this->assertTrue( $useCase->handleNotification( $this->newRequest( 1 ) ) );
	}

	public function testWhenDonationIsNotFound_handlerCreatesOneAndReturnsTrue() {
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( ValidDonation::newIncompletePayPalDonation() );

		$request = $this->newRequest( 123456 );
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

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer(),
			new NullLogger()
		);

		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentStatusIsPending_handlerReturnsFalse() {
		$request = $this->newRequest( 1 );
		$request->setPaymentStatus( 'Pending' );

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

		$request = $this->newRequest( 1 );
		$request->setPaymentStatus( 'Pending' );

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
		$request = $this->newRequest( 1 );
		$request->setTransactionType( 'subscr_modify' );
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

		$request = $this->newRequest( 1 );
		$request->setTransactionType( 'subscr_modify' );

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

		$request = $this->newRequest( 1 );
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

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$mailer,
			new NullLogger()
		);

		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	/**
	 * @return DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )->disableOriginalConstructor()->getMock();
	}

	private function newRequest( int $donationId ) {
		return ( new PayPalNotificationRequest() )
			->setTransactionType( 'express_checkout' )
			->setTransactionId( '61E67681CH3238416' )
			->setPayerId( 'LPLWNMTBWMFAY' )
			->setSubscriberId( '8RHHUM3W3PRH7QY6B59' )
			->setPayerEmail( 'payer.email@address.com' )
			->setPayerStatus( 'verified' )
			->setPayerFirstName( 'Generous' )
			->setPayerLastName( 'Donor' )
			->setPayerAddressName( 'Generous Donor' )
			->setPayerAddressStreet( '123, Some Street' )
			->setPayerAddressPostalCode( '123456' )
			->setPayerAddressCity( 'Some City' )
			->setPayerAddressCountryCode( 'DE' )
			->setPayerAddressStatus( 'confirmed' )
			->setDonationId( $donationId )
			->setToken( 'my_secret_token' )
			->setCurrencyCode( 'EUR' )
			->setTransactionFee( Euro::newFromCents( 27 ) )
			->setAmountGross( Euro::newFromCents( 500 ) )
			->setSettleAmount( Euro::newFromCents( 123 ) )
			->setPaymentTimestamp( '20:12:59 Jan 13, 2009 PST' )
			->setPaymentStatus( 'Completed' )
			->setPaymentType( 'instant' );
	}

}
