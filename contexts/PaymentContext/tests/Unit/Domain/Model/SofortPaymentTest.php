<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\PaymentContext\Domain\Model;

use DateTime;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;

class SofortPaymentTest extends TestCase {

	public function testAccessors(): void {
		$sofortPayment = new SofortPayment( 'lorem' );
		$this->assertSame( 'SUB', $sofortPayment->getType() );
		$this->assertSame( 'lorem', $sofortPayment->getBankTransferCode() );
		$this->assertNull( $sofortPayment->getConfirmedAt() );
		$sofortPayment->setConfirmedAt( new DateTime( '2001-12-24T17:30:00Z' ) );
		$this->assertEquals( new DateTime( '2001-12-24T17:30:00Z' ), $sofortPayment->getConfirmedAt() );
	}
}
