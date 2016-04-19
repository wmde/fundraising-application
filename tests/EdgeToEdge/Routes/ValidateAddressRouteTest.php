<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateAddressRouteTest extends WebRouteTestCase {

	public function testGivenValidAddress_validationReturnsSuccess() {
		$this->createEnvironment( [], function ( Client $client ) {

			$client->followRedirects( false );

			$client->request(
				'POST',
				'/validate-address',
				$this->newPersonFormInput()
			);

			$response = $client->getResponse();

			$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $response );
		} );
	}

	public function testGivenInvalidCompanyAddress_validationReturnsErrorMessage() {
		$this->createEnvironment( [], function ( Client $client ) {

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
					'firma' => 'field_required'
				]
			];
			$this->assertJsonSuccessResponse( $expectedResponse, $response );

		} );
	}

	private function newPersonFormInput() {
		return [
			'adresstyp' => 'person',
			'anrede' => 'Frau',
			'titel' => 'Prof. Dr.',
			'firma' => '',
			'vorname' => 'Karla',
			'nachname' => 'Kennichnich',
			'strasse' => 'Lehmgasse 12',
			'plz' => '12345',
			'ort' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
		];
	}

	private function newCompanyWithMissingNameFormInput() {
		return [
			'adresstyp' => 'firma',
			'anrede' => 'Frau',
			'titel' => 'Prof. Dr.',
			'firma' => '',
			'vorname' => 'Karla',
			'nachname' => 'Kennichnich',
			'strasse' => 'Lehmgasse 12',
			'plz' => '12345',
			'ort' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
		];
	}

}
