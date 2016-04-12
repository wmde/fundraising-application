<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\EmailAddress
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class EmailAddressTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider unparsableAddressProvider
	 */
	public function testWhenGivenMail_validatorMXValidatesCorrectly( $mailToTest ) {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Given email address could not be parsed' );
		new EmailAddress( $mailToTest );
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
