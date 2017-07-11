<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\PaymentContext\DataAccess\Sofort\Transfer;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer\Response;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer\Response
 */
class ResponseTest extends TestCase {

	public function testAccessors(): void {
		$response = new Response();

		$this->assertSame( '', $response->getPaymentUrl() );
		$this->assertSame( '', $response->getTransactionId() );

		$response->setPaymentUrl( 'foo.com' );
		$response->setTransactionId( '12345' );

		$this->assertSame( 'foo.com', $response->getPaymentUrl() );
		$this->assertSame( '12345', $response->getTransactionId() );
	}
}
