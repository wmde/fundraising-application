<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Validation\ValidateAddressController;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( ValidateAddressController::class )]
class ValidateAddressRouteTest extends WebRouteTestCase {

	public function testGivenValidAddress_validationReturnsSuccess(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/validate-address',
			$this->newPersonFormInput()
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $response );
	}

	public function testGivenInvalidCompanyAddress_validationReturnsErrorMessage(): void {
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
				'companyName' => 'missing'
			]
		];
		$this->assertJsonSuccessResponse( $expectedResponse, $response );
	}

	public function testGivenAnonymousAddress_validationReturnsSuccess(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/validate-address',
			$this->newAnonymousFormInput()
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $response );
	}

	/**
	 * @return array<string, string>
	 */
	private function newPersonFormInput(): array {
		return [
			'addressType' => AddressType::PERSON,
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

	/**
	 * @return array<string, string>
	 */
	private function newCompanyWithMissingNameFormInput(): array {
		return [
			'addressType' => AddressType::LEGACY_COMPANY,
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

	/**
	 * @return array<string, string>
	 */
	private function newAnonymousFormInput(): array {
		return [
			'addressType' => 'anonymous'
		];
	}

}
