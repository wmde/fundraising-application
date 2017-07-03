<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Sofort\Transfer;

use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase {

	public function testResponse(): void {
		$response = new Response();

		$this->assertSame( '', $response->getPaymentUrl() );
		$this->assertSame( '', $response->getTransactionId() );

		$response->setPaymentUrl( 'foo.com' );
		$response->setTransactionId( '12345' );

		$this->assertEquals( 'foo.com', $response->getPaymentUrl() );
		$this->assertEquals( '12345', $response->getTransactionId() );
	}
}