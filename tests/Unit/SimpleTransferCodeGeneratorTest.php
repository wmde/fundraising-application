<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\SimpleTransferCodeGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\SimpleTransferCodeGenerator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SimpleTransferCodeGeneratorTest extends \PHPUnit\Framework\TestCase {

	public function testGenerateBankTransferCode_matchesRegex(): void {
		$generator = new SimpleTransferCodeGenerator();
		$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $generator->generateTransferCode() );
	}

}
