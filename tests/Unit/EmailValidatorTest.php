<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\Infrastructure\DomainNameValidator;
use WMDE\Fundraising\Frontend\Infrastructure\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\EmailValidator
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class EmailValidatorTest extends \PHPUnit_Framework_TestCase {

	private function newStubDomainValidator(): DomainNameValidator {
		return new class() implements DomainNameValidator {
			public function isValid( string $domain ): bool {
				return in_array( $domain, [
					'wikimedia.de',
					'nick.berlin',
					'xn--triebwerk-grn-7ob.de',
					'xn--4gbrim.xn----ymcbaaajlc6dj7bxne2c.xn--wgbh1c'
				] );
			}
		};
	}

	/**
	 * @dataProvider fullyValidEmailProvider
	 */
	public function testGivenValidMail_validationWithDomainNameCheckSucceeds( $validEmail ) {
		$mailValidator = new EmailValidator( $this->newStubDomainValidator() );

		$this->assertTrue( $mailValidator->validate( $validEmail )->isSuccessful() );
	}

	public function fullyValidEmailProvider() {
		return [
			[ 'christoph.fischer@wikimedia.de' ],
			[ 'test@nick.berlin' ],
			[ 'A-Za-z0-9.!#$%&\'*+-/=?^_`{|}~info@nick.berlin' ],
			[ 'info@triebwerk-grün.de' ],
			[ 'info@triebwerk-grün.de' ],
			[ 'info@موقع.وزارة-الاتصالات.مصر' ],
		];
	}

	/**
	 * @dataProvider emailWithInvalidDomainProvider
	 */
	public function testGivenMailWithInvalidDomain_validationWithDomainNameCheckFails( $invalidEmail ) {
		$mailValidator = new EmailValidator( $this->newStubDomainValidator() );

		$this->assertFalse( $mailValidator->validate( $invalidEmail )->isSuccessful() );
	}

	public function emailWithInvalidDomainProvider() {
		return [
			[ 'chrifi.asfsfas.de  ' ],
			[ ' ' ],
			[ 'fibor@fgagaadadfafasfasfasfasffasfsfe.com' ],
			[ 'hllo909a()_9a=f9@dsafadsff' ],
			[ 'christoph.fischer@wikimedia.de ' ],
			[ 'christoph.füscher@wikimedia.de ' ],
		];
	}

	/**
	 * @dataProvider emailWithInvalidFormatProvider
	 */
	public function testGivenMailWithInvalidFormat_validationWithoutDomainCheckFails( $invalidEmail ) {
		$mailValidator = new EmailValidator( new NullDomainNameValidator() );

		$this->assertFalse( $mailValidator->validate( $invalidEmail )->isSuccessful() );
	}

	public function emailWithInvalidFormatProvider() {
		return [
			[ 'chrifi.asfsfas.de  ' ],
			[ ' ' ],
			[ 'hllo909a()_9a=f9@dsafadsff' ],
			[ 'christoph.fischer@wikimedia.de ' ],
			[ 'christoph.füscher@wikimedia.de ' ],
		];
	}

}
