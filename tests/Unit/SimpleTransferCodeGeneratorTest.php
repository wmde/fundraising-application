<?php

namespace WMDE\Fundraising\Store\Tests;

use WMDE\Fundraising\Frontend\Domain\SimpleTransferCodeGenerator;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\SimpleTransferCodeGenerator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SimpleTransferCodeGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testGenerateBankTransferCode_matchesRegex() {
		$generator = new SimpleTransferCodeGenerator();
		$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $generator->generateTransferCode() );
	}

}
