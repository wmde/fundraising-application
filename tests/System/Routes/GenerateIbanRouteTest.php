<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GenerateIbanRouteTest extends WebRouteTestCase {

	const INVALID_ACCOUNT_NUMBER = '1015754241';
	const VALID_ACCOUNT_NUMBER = '1015754243';

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
		parent::setUp();
	}

	public function testGivenInvalidBankAccountData_failureResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/generate-iban',
			[
				'accountNumber' => self::INVALID_ACCOUNT_NUMBER,
				'bankCode' => '20050550',
			]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

	public function testGivenValidBankAccountData_successResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/generate-iban',
			[
				'accountNumber' => self::VALID_ACCOUNT_NUMBER,
				'bankCode' => '20050550',
			]
		);

		$this->assertJsonSuccessResponse(
			[
				'status' => 'OK',
				'bic' => 'HASPDEHHXXX',
				'iban' => 'DE76200505501015754243',
				'account' => '1015754243',
				'bankCode' => '20050550',
				'bankName' => 'Hamburger Sparkasse',
			],
			$client->getResponse()
		);
	}

}
