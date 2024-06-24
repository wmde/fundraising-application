<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\Controllers\Payment\BankDataToIbanController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( BankDataToIbanController::class )]
class BankDataToIbanControllerTest extends WebRouteTestCase {

	private const PATH = '/generate-iban';

	public function testGivenValidRequest_applicationReturnsBankData(): void {
		$client = $this->createClient();

		$client->request(
			Request::METHOD_GET,
			self::PATH,
			[
				'accountNumber' => '1015754243',
				'bankCode' => '20050550',
			]
		);

		$expected = [
			"status" => "OK",
			"bic" => "HASPDEHHXXX",
			"iban" => "DE76200505501015754243",
			"account" => "1015754243",
			"bankCode" => "20050550",
			"bankName" => "Hamburger Sparkasse"
		];

		$this->assertJsonSuccessResponse( $expected, $client->getResponse() );
	}

	public function testGivenValidRequest_applicationReturnsError(): void {
		$client = $this->createClient();

		$client->request(
			Request::METHOD_GET,
			self::PATH,
			[
				'accountNumber' => '',
				'bankCode' => '',
			]
		);

		$this->assertJsonSuccessResponse( [ "status" => "ERR" ], $client->getResponse() );
	}
}
