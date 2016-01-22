<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\DomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\MailValidator
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class MailValidatorTest extends \PHPUnit_Framework_TestCase {

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
		$mailValidator = new MailValidator( $this->newStubDomainValidator() );

		$this->assertTrue( $mailValidator->validate( $validEmail ) );
	}

	public function fullyValidEmailProvider() {
		return array(
			array( 'christoph.fischer@wikimedia.de' ),
			array( 'test@nick.berlin' ),
			array( 'A-Za-z0-9.!#$%&\'*+-/=?^_`{|}~info@nick.berlin' ),
			array( 'info@triebwerk-grün.de' ),
			array( 'info@triebwerk-grün.de' ),
			array( 'info@موقع.وزارة-الاتصالات.مصر' ),
		);
	}

	/**
	 * @dataProvider emailWithInvalidDomainProvider
	 */
	public function testGivenMailWithInvalidDomain_validationWithDomainNameCheckFails( $invalidEmail ) {
		$mailValidator = new MailValidator( $this->newStubDomainValidator() );

		$this->assertFalse( $mailValidator->validate( $invalidEmail ) );
	}

	public function emailWithInvalidDomainProvider() {
		return [
			array( 'chrifi.asfsfas.de  ' ),
			array( ' ' ),
			array( 'fibor@fgagaadadfafasfasfasfasffasfsfe.com' ),
			array( 'hllo909a()_9a=f9@dsafadsff' ),
			array( 'christoph.fischer@wikimedia.de ' ),
			array( 'christoph.füscher@wikimedia.de ' ),
		];
	}

	/**
	 * @dataProvider emailWithInvalidFormatProvider
	 */
	public function testGivenMailWithInvalidFormat_validationWithoutDomainCheckFails( $invalidEmail ) {
		$mailValidator = new MailValidator( new NullDomainNameValidator() );

		$this->assertFalse( $mailValidator->validate( $invalidEmail ) );
	}

	public function emailWithInvalidFormatProvider() {
		return [
			array( 'chrifi.asfsfas.de  ' ),
			array( ' ' ),
			array( 'hllo909a()_9a=f9@dsafadsff' ),
			array( 'christoph.fischer@wikimedia.de ' ),
			array( 'christoph.füscher@wikimedia.de ' ),
		];
	}

}
