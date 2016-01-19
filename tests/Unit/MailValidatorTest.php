<?php

declare(strict_types=1);

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\MailValidator;

/**
 * @covers WMDE\Fundraising\Frontend\MailValidator
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class MailValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider emailTestProviderMX
	 */
	public function testWhenGivenMail_validatorMXValidatesCorrectly( $mailToTest, $resultExpected ) {
		$mailValidator = new MailValidator( true );

		$this->assertSame( $mailValidator->validateMail( $mailToTest ), $resultExpected );
	}

	public function emailTestProviderMX() {
		return array(
			array( 'chrifi.asfsfas.de  ', false ),
			array( ' ', false ),
			array( 'fibor@fgagaadadfafasfasfasfasffasfsfe.com', false ),
			array( 'hllo909a()_9a=f9@dsafadsff', false ),
			array( 'christoph.fischer@wikimedia.de ', false ),
			array( 'christoph.füscher@wikimedia.de ', false ),

			array( 'christoph.fischer@wikimedia.de', true ),
			array( 'test@nick.berlin', true ),
			array( 'A-Za-z0-9.!#$%&\'*+-/=?^_`{|}~info@nick.berlin', true ),
			array( 'info@triebwerk-grün.de', true ),
			array( 'info@triebwerk-grün.de', true ),
			array( 'info@موقع.وزارة-الاتصالات.مصر', true ),
		);
	}

	/**
	 * @dataProvider emailTestProviderNoMX
	 */
	public function testWhenGivenMail_validatorNoMXValidatesCorrectly( $mailToTest, $resultExpected ) {
		$mailValidator = new MailValidator( false );

		$this->assertSame( $mailValidator->validateMail( $mailToTest ), $resultExpected );
	}

	public function emailTestProviderNoMX() {
		return array(
			array( 'chrifi.asfsfas.de  ', false ),
			array( ' ', false ),
			array( 'hllo909a()_9a=f9@dsafadsff', false ),
			array( 'christoph.fischer@wikimedia.de ', false ),
			array( 'christoph.füscher@wikimedia.de ', false ),

			array( 'fibor@fgagaadadfafasfasfasfasffasfsfe.com', true ),
			array( 'christoph.fischer@wikimedia.de', true ),
			array( 'test@test.email', true ),
			array( 'A-Za-z0-9.!#$%&\'*+-/=?^_`{|}~info@test.email', true ),
			array( 'info@triebwerk-grün.de', true ),
			array( 'info@موقع.وزارة-الاتصالات.مصر', true ),
		);
	}
}
