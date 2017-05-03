<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateAddressRouteTest extends WebRouteTestCase {

	public function testGivenValidAddress_validationReturnsSuccess() {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/validate-address',
			$this->newPersonFormInput()
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( ['status' => 'OK'], $response );
	}

	public function testGivenInvalidCompanyAddress_validationReturnsErrorMessage() {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/validate-address',
			$this->newCompanyWithMissingNameFormInput()
		);

		$response = $client->getResponse();

		$expectedResponse = [
			'status' => 'ERR',
			'messages' => [
				'company' => 'field_required'
			]
		];
		$this->assertJsonSuccessResponse( $expectedResponse, $response );
	}

	private function newPersonFormInput() {
		return [
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'company' => '',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
		];
	}

	private function newCompanyWithMissingNameFormInput() {
		return [
			'addressType' => 'firma',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'company' => '',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
		];
	}

}
