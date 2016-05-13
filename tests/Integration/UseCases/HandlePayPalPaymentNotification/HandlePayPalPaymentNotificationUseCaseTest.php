<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
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
		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) ),
			new FailingDonationAuthorizer(),
			$this->getMailer()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationFails_handlerReturnsFalse() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new FailingDonationAuthorizer(),
			$this->getMailer()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_handlerReturnsTrue() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenDonationIsNotFound_handlerCreatesOneAndReturnsTrue() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 123456 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenPaymentTypeIsNonPayPal_handlerReturnsFalse() {
		$donation = ValidDonation::newDirectDebitDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase(
			$fakeRepository,
			new SucceedingDonationAuthorizer(),
			$this->getMailer()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
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
		$useCase = new HandlePayPalPaymentNotificationUseCase( $fakeRepository, new SucceedingDonationAuthorizer(), $mailer );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );
		$mailer = $this->getMailer();

		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->willThrowException( new \RuntimeException( 'Oh noes!' ) );
		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase( $fakeRepository, new SucceedingDonationAuthorizer(), $mailer );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	private function getMailer() {
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
