<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Sofort\Transfer;

use PHPUnit\Framework\TestCase;
use Sofort\SofortLib\Sofortueberweisung;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Client;
use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Request;
use RuntimeException;

class ClientTest extends TestCase {

	public function testGet(): void {
		$client = new Client( '47:11:00' );

		$amount = Euro::newFromCents( 500 );
		$amountConvertedToFloat = $amount->getEuroFloat();

		$api = $this->createMock( Sofortueberweisung::class );
		$api
			->expects( $this->once() )
			->method( 'setAmount' )
			->with( $amountConvertedToFloat )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setCurrencyCode' )
			->with( 'EUR' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setReason' )
			->with( 'Donation', '529836' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setSuccessUrl' )
			->with( 'https://us.org/yes?id=529836&accessToken=letmein', true )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setAbortUrl' )
			->with( 'https://us.org/no' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setNotificationUrl' )
			->with( 'https://us.org/callback' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'sendRequest' )
			->willReturn( null );
		$api
			->expects( $this->once() )
			->method( 'isError' )
			->willReturn( false );
		$api
			->expects( $this->never() )
			->method( 'getError' );
		$api
			->expects( $this->once() )
			->method( 'getTransactionId' )
			->willReturn( 'tr4ns4ct10n' );
		$api
			->expects( $this->once() )
			->method( 'getPaymentUrl' )
			->willReturn( 'https://awsomepaymentprovider.tld/784trhhrf4' );

		$client->setApi( $api );

		$request = new Request();
		$request->setAmount( $amount );
		$request->setCurrencyCode( 'EUR' );
		$request->setReasons( [ 'Donation', '529836' ] );
		$request->setSuccessUrl( 'https://us.org/yes?id=529836&accessToken=letmein' );
		$request->setAbortUrl( 'https://us.org/no' );
		$request->setNotificationUrl( 'https://us.org/callback' );
		$response = $client->get( $request );

		$this->assertSame( 'https://awsomepaymentprovider.tld/784trhhrf4', $response->getPaymentUrl() );
		$this->assertSame( 'tr4ns4ct10n', $response->getTransactionId() );
	}

	public function testWhenApiReturnsErrorAnExceptionWithApiErrorMessageIsThrown(): void {
		$client = new Client( '47:11:00' );

		$api = $this->createMock( Sofortueberweisung::class );

		$api
			->expects( $this->once() )
			->method( 'isError' )
			->willReturn( true );
		$api
			->expects( $this->once() )
			->method( 'getError' )
			->willReturn( 'boo boo' );

		$client->setApi( $api );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'boo boo' );

		$request = new Request();
		$request->setAmount( Euro::newFromCents( 500 ) );
		$request->setCurrencyCode( 'EUR' );
		$request->setReasons( [ 'Donation', '529836' ] );
		$request->setSuccessUrl( 'https://us.org/yes?id=529836&accessToken=letmein' );
		$request->setAbortUrl( 'https://us.org/no' );
		$request->setNotificationUrl( 'https://us.org/callback' );

		$client->get( $request );
	}
}