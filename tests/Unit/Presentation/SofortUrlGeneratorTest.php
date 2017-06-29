<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use Sofort\SofortLib\Sofortueberweisung;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\SofortUrlConfig;
use WMDE\Fundraising\Frontend\Presentation\SofortUrlGenerator;

class SofortUrlGeneratorTest extends TestCase {

	public function testGenerateUrlSuccess(): void {
		$config = SofortUrlConfig::newFromConfig( [
			'config-key' => 'fff',
			'item-name' => 'Donation',
			'return-url' => 'https://us.org/yes',
			'cancel-url' => 'https://us.org/no'
		] );

		$api = $this->createMock( Sofortueberweisung::class );
		$api
			->expects( $this->once() )
			->method( 'setAmount' )
			->with( '5.00' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setCurrencyCode' )
			->with( 'EUR' )
			->willReturnSelf();
		$api
			->expects( $this->once() )
			->method( 'setReason' )
			->with( 'Donation', 529836 )
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

		$sut = new SofortUrlGenerator( $config, $api );
		$this->assertSame(
			'https://awsomepaymentprovider.tld/784trhhrf4',
			$sut->generateUrl( 529836, Euro::newFromCents( 500 ), 'letmein' )
		);
	}

	public function testGenerateUrlApiError(): void {
		$config = SofortUrlConfig::newFromConfig( [
			'config-key' => 'ggg',
			'item-name' => 'Buy',
			'return-url' => 'https://irreleva.nt',
			'cancel-url' => 'http://irreleva.nt'
		] );

		$api = $this->createMock( Sofortueberweisung::class );

		$api
			->expects( $this->once() )
			->method( 'isError' )
			->willReturn( true );
		$api
			->expects( $this->once() )
			->method( 'getError' )
			->willReturn( 'boo boo' );

		$sut = new SofortUrlGenerator( $config, $api );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Could not generate Sofort URL: boo boo' );

		$sut->generateUrl( 529837, Euro::newFromCents( 300 ), 'letmein' );
	}
}
