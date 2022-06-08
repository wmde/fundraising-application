<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @requires extension konto_check
 */
class GenerateIbanRouteTest extends WebRouteTestCase {

	private const INVALID_ACCOUNT_NUMBER = '1015754241';
	private const VALID_ACCOUNT_NUMBER = '1015754243';

	public function testGivenInvalidBankAccountData_failureResponseIsReturned(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

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

	public function testGivenValidBankAccountData_successResponseIsReturned(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

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
