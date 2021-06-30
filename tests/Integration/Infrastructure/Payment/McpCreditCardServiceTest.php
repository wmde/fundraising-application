<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Infrastructure\Payment;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\McpCreditCardService;
use WMDE\Fundraising\PaymentContext\Infrastructure\CreditCardExpiry;
use WMDE\Fundraising\PaymentContext\Infrastructure\CreditCardExpiryFetchingException;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Payment\McpCreditCardService
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class McpCreditCardServiceTest extends TestCase {

	private const ACCESS_KEY = 'pink fluffy unicorns';
	private const CUSTOMER_ID = '31333333333333333337';

	private const EXPIRY_MONTH = 5;
	private const EXPIRY_YEAR = 2020;

	private const VALID_RETURN_DATA = [
		'expiryMonth' => self::EXPIRY_MONTH,
		'expiryYear' => self::EXPIRY_YEAR,
	];

	private static int $oldErrorReportingLevel;

	public static function setUpBeforeClass(): void {
		// Version <=1.25 of the Mcp library uses default parameters before parameters without defaults,
		// which is deprecated in PHP 8. This causes 20+ deprecation warnings during the unit tests.
		// To keep the unit test output useful, we suppress the warnings for now (and have notified the provider
		// of the library).
		// When the deprecation has been fixed, revert the commit that introduced this workaround
		self::$oldErrorReportingLevel = error_reporting( E_ALL ^ E_DEPRECATED );
	}

	public static function tearDownAfterClass(): void {
		error_reporting( self::$oldErrorReportingLevel );
	}

	public function testMicroPaymentServiceGetsCalledWithAccessKeyAndCustomerId(): void {
		$microPaymentServiceMock = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceMock->expects( $this->once() )
			->method( 'creditcardDataGet' )
			->with(
				$this->equalTo( self::ACCESS_KEY ),
				$this->equalTo( true ),
				$this->equalTo( self::CUSTOMER_ID )
			)
			->willReturn( self::VALID_RETURN_DATA );

		$creditCardService = new McpCreditCardService( $microPaymentServiceMock, self::ACCESS_KEY, true );
		$creditCardService->getExpirationDate( self::CUSTOMER_ID );
	}

	/**
	 * @return \IMcpCreditcardService_v1_5 & MockObject
	 */
	private function getMicroPaymentServiceTestDouble(): \IMcpCreditcardService_v1_5 {
		return $this->createMock( \IMcpCreditcardService_v1_5::class );
	}

	public function testWhenValidDataIsReturned_creditCardExpiryIsCreated(): void {
		$microPaymentServiceStub = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceStub->expects( $this->any() )
			->method( 'creditcardDataGet' )
			->willReturn( self::VALID_RETURN_DATA );

		$creditCardService = new McpCreditCardService( $microPaymentServiceStub, self::ACCESS_KEY, true );

		$this->assertEquals(
			new CreditCardExpiry( self::EXPIRY_MONTH, self::EXPIRY_YEAR ),
			$creditCardService->getExpirationDate( self::CUSTOMER_ID )
		);
	}

	/**
	 * @dataProvider invalidReturnDataProvider
	 */
	public function testWhenInvalidDataIsReturned_exceptionIsThrown( array $invalidReturnData ): void {
		$microPaymentServiceStub = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceStub->expects( $this->any() )
			->method( 'creditcardDataGet' )
			->willReturn( $invalidReturnData );

		$creditCardService = new McpCreditCardService( $microPaymentServiceStub, self::ACCESS_KEY, true );

		$this->expectException( CreditCardExpiryFetchingException::class );
		$creditCardService->getExpirationDate( self::CUSTOMER_ID );
	}

	public function invalidReturnDataProvider(): array {
		return [
			[ [
				'expiryMonth' => 'potato',
				'expiryYear' => self::EXPIRY_YEAR,
			] ],
			[ [
				'expiryMonth' => 0,
				'expiryYear' => self::EXPIRY_YEAR,
			] ],
			[ [
				'expiryMonth' => 13,
				'expiryYear' => self::EXPIRY_YEAR,
			] ]
		];
	}

}
