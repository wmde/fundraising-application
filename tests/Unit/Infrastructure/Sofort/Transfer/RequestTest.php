<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Sofort\Transfer;

use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Request;
use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;

class RequestTest extends TestCase {

	public function testAccessors(): void {
		$request = new Request();

		$amount = Euro::newFromCents( 999 );
		$request->setAmount( $amount );
		$this->assertSame( $amount, $request->getAmount() );

		$request->setCurrencyCode( 'EUR' );
		$this->assertSame( 'EUR', $request->getCurrencyCode() );

		$request->setReasons( [ 'a', 'b' ] );
		$this->assertSame( [ 'a', 'b' ], $request->getReasons() );

		$request->setSuccessUrl( 'success' );
		$this->assertSame( 'success', $request->getSuccessUrl() );

		$request->setAbortUrl( 'abort' );
		$this->assertSame( 'abort', $request->getAbortUrl() );

		$request->setNotificationUrl( 'notify' );
		$this->assertSame( 'notify', $request->getNotificationUrl() );
	}
}

