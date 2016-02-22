<?php

namespace WMDE\Fundraising\Store\Tests;

use WMDE\Fundraising\Frontend\TransferCodeGenerator;

/**
 * @covers WMDE\Fundraising\Frontend\TransferCodeGenerator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TransferCodeGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testGenerateBankTransferCode_matchesRegex() {
		$generator = new TransferCodeGenerator();
		$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $generator->generateTransferCode() );
	}

}
