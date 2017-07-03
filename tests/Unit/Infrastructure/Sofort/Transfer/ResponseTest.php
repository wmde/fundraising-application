<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Sofort\Transfer;

use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Response
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
