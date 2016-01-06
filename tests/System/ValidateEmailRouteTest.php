<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

use Symfony\Component\HttpFoundation\Response;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidateEmailRouteTest extends SystemTestCase {

	public function testGivenValidEmail_successResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email',
			[ 'email' => 'jeroendedauw@gmail.com' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	private function assertJsonSuccessResponse( $expected, Response $response ) {
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertJson( $response->getContent(), 'response is json' );
		$this->assertSame( $expected, json_decode( $response->getContent(), true ) );
	}

	public function testGivenInvalidEmail_errorResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email',
			[ 'email' => '~=[,,_,,]:3' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

	public function testGivenNoEmail_errorResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email'
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

}
