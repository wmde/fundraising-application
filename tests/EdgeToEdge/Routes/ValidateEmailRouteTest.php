<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Validation\ValidationController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( ValidationController::class )]
class ValidateEmailRouteTest extends WebRouteTestCase {

	public function testGivenValidEmail_successResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-email',
			[ 'email' => 'jeroendedauw@gmail.com' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidEmail_errorResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-email',
			[ 'email' => '~=[,,_,,]:3' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

	public function testGivenNoEmail_errorResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-email'
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

}
