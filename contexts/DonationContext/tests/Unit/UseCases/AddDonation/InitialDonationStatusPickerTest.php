<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\DonationContext\UseCases\AddDonation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\InitialDonationStatusPicker;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\InitialDonationStatusPicker
 */
class InitialDonationStatusPickerTest extends TestCase {
	public function testGetInitialDonationStatus(): void {
		$picker = new InitialDonationStatusPicker();

		$this->assertSame( 'N', $picker( 'BEZ' ) );
		$this->assertSame( 'Z', $picker( 'UEB' ) );
		$this->assertSame( 'Z', $picker( 'SUB' ) );

		$this->assertSame( 'X', $picker( 'MCP' ) );
		$this->assertSame( 'X', $picker( 'foo' ) );
	}
}
