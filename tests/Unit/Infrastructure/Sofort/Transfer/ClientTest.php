<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Sofort\Transfer;

use PHPUnit\Framework\TestCase;
use Sofort\SofortLib\Sofortueberweisung;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Client;
use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Request;
use RuntimeException;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Client
 */
class ClientTest extends TestCase {

	public function testGet(): void {
		$client = new Client( '47:11:00' );

		$amount = Euro::newFromCents( 500 );
		$amountConvertedToFloat = $amount->getEuroFloat();

		$api = $this->createMock( Sofortueberweisung::class );

		$api
			->method( 'setAmount' )
			->with( $amountConvertedToFloat );
		$api
			->method( 'setCurrencyCode' )
			->with( 'EUR' );
		$api
			->method( 'setReason' )
			->with( 'Donation', '529836' );
		$api
			->method( 'setSuccessUrl' )
			->with( 'https://us.org/yes?id=529836&accessToken=letmein', true );
		$api
			->method( 'setAbortUrl' )
			->with( 'https://us.org/no' );
		$api
			->method( 'setNotificationUrl' )
			->with( 'https://us.org/callback' );
		$api
			->method( 'sendRequest' );
		$api
			->method( 'isError' )
			->willReturn( false );
		$api
			->expects( $this->never() )
			->method( 'getError' );
		$api
			->method( 'getTransactionId' )
			->willReturn( 'tr4ns4ct10n' );
		$api
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
			->method( 'isError' )
			->willReturn( true );
		$api
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
