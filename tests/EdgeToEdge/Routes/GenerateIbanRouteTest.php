<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\Presentation\Presenters\IbanPresenter;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( IbanPresenter::class )]
class GenerateIbanRouteTest extends WebRouteTestCase {

	private const INVALID_ACCOUNT_NUMBER = '1015754241';
	private const VALID_ACCOUNT_NUMBER = '1015754243';

	public function testGivenInvalidBankAccountData_failureResponseIsReturned(): void {
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
