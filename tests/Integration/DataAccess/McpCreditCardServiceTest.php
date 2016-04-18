<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use WMDE\Fundraising\Frontend\DataAccess\McpCreditCardService;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardExpiry;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardExpiryFetchingException;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\McpCreditCardService
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class McpCreditCardServiceTest extends \PHPUnit_Framework_TestCase {

	const ACCESS_KEY = 'pink fluffy unicorns';
	const CUSTOMER_ID = '31333333333333333337';

	const EXPIRY_MONTH = 5;
	const EXPIRY_YEAR = 2020;

	const VALID_RETURN_DATA = [
		'expiryMonth' => self::EXPIRY_MONTH,
		'expiryYear' => self::EXPIRY_YEAR,
	];

	public function testMicroPaymentServiceGetsCalledWithAccessKeyAndCustomerId() {
		$microPaymentServiceMock = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceMock->expects( $this->once() )
			->method( 'creditcardDataGet' )
			->with(
				$this->equalTo( self::ACCESS_KEY ),
				$this->equalTo( null ),
				$this->equalTo( self::CUSTOMER_ID )
			)
			->willReturn( self::VALID_RETURN_DATA );

		$creditCardService = new McpCreditCardService( $microPaymentServiceMock, self::ACCESS_KEY );
		$creditCardService->getExpirationDate( self::CUSTOMER_ID );
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|\IMcpCreditcardService_v1_5
	 */
	private function getMicroPaymentServiceTestDouble() {
		return $this->getMock( \IMcpCreditcardService_v1_5::class );
	}

	public function testWhenValidDataIsReturned_creditCardExpiryIsCreated() {
		$microPaymentServiceStub = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceStub->expects( $this->any() )
			->method( 'creditcardDataGet' )
			->willReturn( self::VALID_RETURN_DATA );

		$creditCardService = new McpCreditCardService( $microPaymentServiceStub, self::ACCESS_KEY );

		$this->assertEquals(
			new CreditCardExpiry( self::EXPIRY_MONTH, self::EXPIRY_YEAR ),
			$creditCardService->getExpirationDate( self::CUSTOMER_ID )
		);
	}

	/**
	 * @dataProvider invalidReturnDataProvider
	 */
	public function testWhenInvalidDataIsReturned_exceptionIsThrown( array $invalidReturnData ) {
		$microPaymentServiceStub = $this->getMicroPaymentServiceTestDouble();

		$microPaymentServiceStub->expects( $this->any() )
			->method( 'creditcardDataGet' )
			->willReturn( $invalidReturnData );

		$creditCardService = new McpCreditCardService( $microPaymentServiceStub, self::ACCESS_KEY );

		$this->expectException( CreditCardExpiryFetchingException::class );
		$creditCardService->getExpirationDate( self::CUSTOMER_ID );
	}

	public function invalidReturnDataProvider() {
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