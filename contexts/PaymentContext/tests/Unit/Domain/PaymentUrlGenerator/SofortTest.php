<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Tests\Unit\Domain\PaymentUrlGenerator;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer\Client;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer\Request;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer\Response;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\SofortConfig as SofortUrlConfig;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\Sofort as SofortUrlGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\Sofort
 */
class SofortTest extends TestCase {

	public function testWhenClientReturnsSuccessResponseAUrlIsReturned(): void {
		$config = new SofortUrlConfig( 'Donation', 'https://us.org/yes', 'https://us.org/no' );

		$amount = Euro::newFromCents( 600 );

		$request = new Request();
		$request->setAmount( $amount );
		$request->setCurrencyCode( 'EUR' );
		$request->setReasons( [ 'Donation', 'wx529836' ] );
		$request->setSuccessUrl( 'https://us.org/yes?id=44&accessToken=letmein' );
		$request->setAbortUrl( 'https://us.org/no' );
		$request->setNotificationUrl( '' );

		$response = new Response();
		$response->setTransactionId( '500m1l35' );
		$response->setPaymentUrl( 'https://awsomepaymentprovider.tld/784trhhrf4' );

		$client = $this->createMock( Client::class );
		$client
			->expects( $this->once() )
			->method( 'get' )
			->with( $request )
			->willReturn( $response );

		$urlGenerator = new SofortUrlGenerator( $config, $client );
		$this->assertSame(
			'https://awsomepaymentprovider.tld/784trhhrf4',
			$urlGenerator->generateUrl( 44, 'wx529836', $amount, 'letmein' )
		);
	}

	public function testWhenApiReturnsErrorAnExceptionWithApiErrorMessageIsThrown(): void {
		$config = new SofortUrlConfig( 'Your purchase', 'https://irreleva.nt', 'http://irreleva.nt' );

		$client = $this->createMock( Client::class );

		$client
			->expects( $this->once() )
			->method( 'get' )
			->withAnyParameters()
			->willThrowException( new RuntimeException( 'boo boo' ) );

		$urlGenerator = new SofortUrlGenerator( $config, $client );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not generate Sofort URL: boo boo' );

		$urlGenerator->generateUrl( 23, 'dq529837', Euro::newFromCents( 300 ), 'letmein' );
	}
}
