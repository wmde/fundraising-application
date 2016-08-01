<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress
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

	public function testGetFullAddressReturnsOriginalInput() {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw@gmail.com', $email->getFullAddress() );
	}

	public function testCanGetEmailParts() {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw', $email->getUserName() );
		$this->assertSame( 'gmail.com', $email->getDomain() );
	}

	public function testCanNormalizedDomainName() {
		$email = new EmailAddress( 'info@triebwerk-grün.de' );

		$this->assertSame( 'xn--triebwerk-grn-7ob.de', $email->getNormalizedDomain() );
		$this->assertSame( 'info@xn--triebwerk-grn-7ob.de', $email->getNormalizedAddress() );
	}

	public function testToStringOriginalInput() {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw@gmail.com', (string)$email->getFullAddress() );
	}

}
