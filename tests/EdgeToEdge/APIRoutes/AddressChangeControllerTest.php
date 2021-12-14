<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\APIRoutes;

use Doctrine\ORM\EntityManager;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\API\Donation\AddressChangeController
 */
class AddressChangeControllerTest extends WebRouteTestCase {

	private const DUMMY_DONATION_ID = 0;
	private const INVALID_IDENTIFIER = 'INVALID IDENTIFIER';
	private const INVALID_REQUEST_BODY = [ 'notAField' => 'not a value' ];

	public function testGetWithValidIdentifier_addressChangeIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier, $previousIdentifier ] = $this->makeAddressChangeIdentifiers( $addressChange );
			$expectedResponse = $this->makeExpectedGetResponse( $identifier, $previousIdentifier );

			$response = $this->whenGetRequestIsSubmitted( $client, $identifier );

			$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testGetWithValidPreviousIdentifier_addressChangeIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier, $previousIdentifier ] = $this->makeAddressChangeIdentifiers( $addressChange );
			$expectedResponse = $this->makeExpectedGetResponse( $identifier, $previousIdentifier );

			$response = $this->whenGetRequestIsSubmitted( $client, self::INVALID_IDENTIFIER, $previousIdentifier );

			$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testGetWithMissingToken_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$response = $this->whenGetRequestIsSubmitted( $client, identifier: '' );

			$this->assertSame( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
			$this->assertJsonResponse( [
				'ERR' => 'No route found for "GET http://localhost/api/v1/address_change/"'
			], $response );
		} );
	}

	public function testGetWithInvalidIdentifier_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$response = $this->whenGetRequestIsSubmitted( $client, self::INVALID_IDENTIFIER );

			$this->assertSame( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
			$this->assertJsonResponse( [
				'ERR' => 'address_change_token_not_found',
				'errors' => []
			], $response );
		} );
	}

	public function testPutWithMissingToken_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$response = $this->whenPutRequestIsSubmitted( $client, identifier: '', requestBody: [] );

			$this->assertJsonResponse( [
				'ERR' => 'No route found for "PUT http://localhost/api/v1/address_change/"'
			], $response );
		} );
	}

	public function testPutWithInvalidIdentifier_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$response = $this->whenPutRequestIsSubmitted( $client, self::INVALID_IDENTIFIER, [ 'addressType' => 'person' ] );

			$this->assertJsonResponse( [
				'ERR' => 'address_change_failed',
				'errors' => [
					'Address not found'
				]
			], $response );
		} );
	}

	public function testPutWithEmptyRequestBody_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier ] = $this->makeAddressChangeIdentifiers( $addressChange );

			$response = $this->whenPutRequestIsSubmitted( $client, $identifier, [] );

			$this->assertJsonResponse( [
				'ERR' => 'address_change_empty_request_body',
				'errors' => []
			], $response );
		} );
	}

	public function testPutWithInvalidData_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier ] = $this->makeAddressChangeIdentifiers( $addressChange );

			$response = $this->whenPutRequestIsSubmitted( $client, $identifier, self::INVALID_REQUEST_BODY );

			$this->assertJsonResponse( [
				'ERR' => 'address_change_failed',
				'errors' => [
					'Invalid value for field "Company".'
				]
			], $response );
		} );
	}

	public function testPutWithValidData_successIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			$identifier = $addressChange->getCurrentIdentifier()->__toString();

			$response = $this->whenPutRequestIsSubmitted( $client, $identifier, $this->makeValidPersonSubmitData() );

			$updatedIdentifier = $addressChange->getCurrentIdentifier()->__toString();
			$expectedResponse = $this->makeExpectedValidPersonPutResponse( identifier: $updatedIdentifier, previousIdentifier: $identifier );

			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testPutWithValidCompanyData_successIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = $this->makeAddressChange( $factory );
			$identifier = $addressChange->getCurrentIdentifier()->__toString();

			$response = $this->whenPutRequestIsSubmitted( $client, $identifier, $this->makeValidCompanySubmitData() );

			$updatedIdentifier = $addressChange->getCurrentIdentifier()->__toString();
			$expectedResponse = $this->makeExpectedValidCompanyPutResponse( identifier: $updatedIdentifier, previousIdentifier: $identifier );

			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testPutWithValidData_userIsOptedIntoReceiptByDefault(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$entityManager = $factory->getEntityManager();
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier ] = $this->makeAddressChangeIdentifiers( $addressChange );

			$this->whenPutRequestIsSubmitted( $client, $identifier, $this->makeValidPersonSubmitData() );

			$this->verifyUserIsOptedIntoReceiptByDefault( $entityManager, $addressChange );
		} );
	}

	public function testPutWithValidData_userCanOptOutOfReceiptWhileStillProvidingAnAddress(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$entityManager = $factory->getEntityManager();
			$addressChange = $this->makeAddressChange( $factory );
			[ $identifier ] = $this->makeAddressChangeIdentifiers( $addressChange );
			$putData = array_merge( $this->makeValidPersonSubmitData(), [ 'receiptOptOut' => true ] );

			$this->whenPutRequestIsSubmitted( $client, $identifier, $putData );

			$this->verifyUserCanOptOutOfReceiptWhileStillProvidingAnAddress( $entityManager, $addressChange );
		} );
	}

	private function makeAddressChange( FunFunFactory $factory ): AddressChange {
		$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

		$factory->getEntityManager()->persist( $addressChange );
		$factory->getEntityManager()->flush();
		return $addressChange;
	}

	private function makeAddressChangeIdentifiers( AddressChange $addressChange ): array {
		$identifier = $addressChange->getCurrentIdentifier()->__toString();
		$previousIdentifier = $addressChange->getCurrentIdentifier()->__toString();
		return [ $identifier, $previousIdentifier ];
	}

	private function whenPutRequestIsSubmitted( Client $client, string $identifier, array $requestBody ): object {
		$client->jsonRequest(
			Request::METHOD_PUT,
			'/api/v1/address_change/' . $identifier,
			$requestBody
		);

		return $client->getResponse();
	}

	private function whenGetRequestIsSubmitted( Client $client, string $identifier, string $previousIdentifier = null ): object {
		$client->jsonRequest(
			Request::METHOD_GET,
			'/api/v1/address_change/' . $identifier . ( $previousIdentifier ? "/$previousIdentifier" : '' ),
		);

		return $client->getResponse();
	}

	private function makeExpectedGetResponse( string $identifier, string $previousIdentifier ): array {
		return [
			'identifier' => $identifier,
			'previousIdentifier' => $previousIdentifier,
			'address' => [],
			'donationReceipt' => true,
			'exportState' => AddressChange::EXPORT_STATE_NO_DATA
		];
	}

	private function makeValidPersonSubmitData(): array {
		return [
			'addressType' => 'person',
			'firstName' => 'Graf',
			'lastName' => 'Zahl',
			'salutation' => 'Herr',
			'street' => 'Zählerweg 5',
			'postcode' => '12345',
			'city' => 'Berlin-Zehlendorf',
			'country' => 'DE'
		];
	}

	private function makeValidCompanySubmitData(): array {
		return [
			'addressType' => 'company',
			'company' => 'ACME Company',
			'street' => 'Zählerweg 5',
			'postcode' => '12345',
			'city' => 'Berlin-Zehlendorf',
			'country' => 'DE'
		];
	}

	private function makeExpectedValidPersonPutResponse( string $identifier, string $previousIdentifier ): array {
		return [
			'identifier' => $identifier,
			'previousIdentifier' => $previousIdentifier,
			'address' => [
				'salutation' => 'Herr',
				'company' => '',
				'title' => '',
				'firstName' => 'Graf',
				'lastName' => 'Zahl',
				'street' => 'Zählerweg 5',
				'postcode' => '12345',
				'city' => 'Berlin-Zehlendorf',
				'country' => 'DE',
				'isPersonalAddress' => true,
				'isCompanyAddress' => false
			],
			'donationReceipt' => true,
			'exportState' => AddressChange::EXPORT_STATE_USED_NOT_EXPORTED
		];
	}

	private function makeExpectedValidCompanyPutResponse( string $identifier, string $previousIdentifier ): array {
		return [
			'identifier' => $identifier,
			'previousIdentifier' => $previousIdentifier,
			'address' => [
				'salutation' => '',
				'company' => 'ACME Company',
				'title' => '',
				'firstName' => '',
				'lastName' => '',
				'street' => 'Zählerweg 5',
				'postcode' => '12345',
				'city' => 'Berlin-Zehlendorf',
				'country' => 'DE',
				'isPersonalAddress' => false,
				'isCompanyAddress' => true
			],
			'donationReceipt' => true,
			'exportState' => AddressChange::EXPORT_STATE_USED_NOT_EXPORTED
		];
	}

	private function verifyUserIsOptedIntoReceiptByDefault( EntityManager $entityManager, AddressChange $addressChange ): void {
		$entityManager->clear( AddressChange::class );
		$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find(
			$addressChange->getId()
		);
		$this->assertTrue(
			$addressChangeAfterRequest->isOptedIntoDonationReceipt(),
			'Donor should be opted into donation receipt'
		);
	}

	private function verifyUserCanOptOutOfReceiptWhileStillProvidingAnAddress( EntityManager $entityManager, AddressChange $addressChange ): void {
		$entityManager->clear( AddressChange::class );
		$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find(
			$addressChange->getId()
		);
		$this->assertFalse(
			$addressChangeAfterRequest->isOptedIntoDonationReceipt(),
			'Donor should be opted out of donation receipt'
		);
	}
}
