<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\MailAddress
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MailAddressTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider unparsableAddressProvider
	 */
	public function testWhenGivenMail_validatorMXValidatesCorrectly( $mailToTest ) {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Given email address could not be parsed' );
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
