<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\PaymentContext\Domain\Model;

use DateTime;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;

class SofortPaymentTest extends TestCase {

	public function testInitialProperties(): void {
		$sofortPayment = new SofortPayment( 'lorem' );
		$this->assertSame( 'SUB', $sofortPayment->getType() );
		$this->assertSame( 'lorem', $sofortPayment->getBankTransferCode() );
		$this->assertNull( $sofortPayment->getConfirmedAt() );
	}

	public function testConfirmedAcceptsDateTime(): void {
		$sofortPayment = new SofortPayment( 'lorem' );
		$sofortPayment->setConfirmedAt( new DateTime( '2001-12-24T17:30:00Z' ) );
		$this->assertEquals( new DateTime( '2001-12-24T17:30:00Z' ), $sofortPayment->getConfirmedAt() );
	}

	public function testIsConfirmedPayment_newPaymentIsNotConfirmed(): void {
		$sofortPayment = new SofortPayment( 'ipsum' );
		$this->assertFalse( $sofortPayment->isConfirmedPayment() );
	}

	public function testIsConfirmedPayment_settingPaymentDateConfirmsPayment(): void {
		$sofortPayment = new SofortPayment( 'ipsum' );
		$sofortPayment->setConfirmedAt( new DateTime( 'now' ) );
		$this->assertTrue( $sofortPayment->isConfirmedPayment() );
	}
}
