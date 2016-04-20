<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\HandlePayPalPaymentNotification;

use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
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
			new FailingDonationAuthorizer()
		);
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationFails_handlerReturnsFalse() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase( $fakeRepository, new FailingDonationAuthorizer() );
		$this->assertFalse( $useCase->handleNotification( $request ) );
	}

	public function testWhenAuthorizationSucceeds_handlerReturnsTrue() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 1 );
		$useCase = new HandlePayPalPaymentNotificationUseCase( $fakeRepository, new SucceedingDonationAuthorizer() );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	public function testWhenDonationIsNotFound_handlerCreatesOneAndReturnsTrue() {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$fakeRepository = new FakeDonationRepository();
		$fakeRepository->storeDonation( $donation );

		$request = $this->newRequest( 123456 );
		$useCase = new HandlePayPalPaymentNotificationUseCase( $fakeRepository, new SucceedingDonationAuthorizer() );
		$this->assertTrue( $useCase->handleNotification( $request ) );
	}

	private function newRequest( int $donationId ) {
		return ( new PayPalNotificationRequest() )
			->setTransactionType( 'transaction_type' )
			->setTransactionId( 'transaction_id' )
			->setPayerId( 'payer_id' )
			->setPayerEmail( 'payer.email@address.com' )
			->setPayerStatus( 'payer_status' )
			->setPayerFirstName( 'first_name' )
			->setPayerLastName( 'last_name' )
			->setPayerAddressName( 'address_name' )
			->setPayerAddressStreet( 'address_street' )
			->setPayerAddressPostalCode( 'address_zip' )
			->setPayerAddressCity( 'address_city' )
			->setPayerAddressCountryCode( 'address_country_code' )
			->setPayerAddressStatus( 'address_status' )
			->setDonationId( $donationId )
			->setToken( 'token' )
			->setCurrencyCode( 'mc_currency' )
			->setTransactionFee( Euro::newFromCents( 27 ) )
			->setAmountGross( Euro::newFromCents( 500 ) )
			->setPaymentTimestamp( 'payment_date' )
			->setPaymentStatus( 'payment_status' )
			->setPaymentType( 'payment_type' );
	}

}
