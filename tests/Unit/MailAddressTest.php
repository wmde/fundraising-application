<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\MailAddress
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MailAddressTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider unparsableAddressProvider
	 */
	public function testWhenGivenMail_validatorMXValidatesCorrectly( $mailToTest ) {
		$this->setExpectedException( \InvalidArgumentException::class );
		new MailAddress( $mailToTest );
	}

	public function unparsableAddressProvider() {
		return [
			[ 'just.testing' ],
			[ 'can.you@deliver@this' ],
			[ '' ],
			[ ' ' ]
		];
	}

}
