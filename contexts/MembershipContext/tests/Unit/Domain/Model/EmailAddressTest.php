<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Unit\Domain\Model;

use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EmailAddressTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider unparsableAddressProvider
	 */
	public function testWhenGivenMail_validatorMXValidatesCorrectly( $mailToTest ): void {
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

	public function testWhenDomainIsEmpty_constructorThrowsException(): void {
		$this->expectException( \InvalidArgumentException::class );
		new EmailAddress( 'jeroendedauw@' );
	}

	public function testGetFullAddressReturnsOriginalInput(): void {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw@gmail.com', $email->getFullAddress() );
	}

	public function testCanGetEmailParts(): void {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw', $email->getUserName() );
		$this->assertSame( 'gmail.com', $email->getDomain() );
	}

	public function testCanNormalizedDomainName(): void {
		$email = new EmailAddress( 'info@triebwerk-grün.de' );

		$this->assertSame( 'xn--triebwerk-grn-7ob.de', $email->getNormalizedDomain() );
		$this->assertSame( 'info@xn--triebwerk-grn-7ob.de', $email->getNormalizedAddress() );
	}

	public function testInvalidDomainNamesAreNormalizedToEmpty(): void {
		$email = new EmailAddress( 'oh_boy@...' );

		$this->assertSame( '', $email->getNormalizedDomain() );
		$this->assertSame( 'oh_boy@', $email->getNormalizedAddress() );
	}

	public function testToStringOriginalInput(): void {
		$email = new EmailAddress( 'jeroendedauw@gmail.com' );

		$this->assertSame( 'jeroendedauw@gmail.com', (string)$email->getFullAddress() );
	}

}
