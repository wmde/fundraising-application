<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
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
