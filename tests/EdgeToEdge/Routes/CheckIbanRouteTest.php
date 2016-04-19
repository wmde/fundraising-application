<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CheckIbanRouteTest extends WebRouteTestCase {

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
			'/check-iban',
			[
				'iban' => 'not a valid IBAN!',
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
			'/check-iban',
			[
				'iban' => 'DE76200505501015754243',
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
