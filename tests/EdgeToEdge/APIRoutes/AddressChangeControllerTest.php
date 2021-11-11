<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\APIRoutes;

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

	public function testGetWithValidIdentifier_addressChangeIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$factory->getEntityManager()->persist( $addressChange );
			$factory->getEntityManager()->flush();

			$identifier = $addressChange->getCurrentIdentifier()->__toString();

			$client->jsonRequest(
				Request::METHOD_GET,
				'/api/v1/address_change/' . $identifier,
			);

			$response = $client->getResponse();

			$expectedResponse = [
				'identifier' => $identifier,
				'previousIdentifier' => $addressChange->getPreviousIdentifier()->__toString(),
				'address' => [],
				'donationReceipt' => true,
				'exportState' => AddressChange::EXPORT_STATE_NO_DATA
			];

			$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testGetWithValidPreviousIdentifier_addressChangeIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$factory->getEntityManager()->persist( $addressChange );
			$factory->getEntityManager()->flush();

			$previousIdentifier = $addressChange->getCurrentIdentifier()->__toString();

			$client->jsonRequest(
				Request::METHOD_GET,
				'/api/v1/address_change/NOT A VALID IDENTIFIER/' . $previousIdentifier,
			);

			$response = $client->getResponse();

			$expectedResponse = [
				'identifier' => $addressChange->getCurrentIdentifier()->__toString(),
				'previousIdentifier' => $previousIdentifier,
				'address' => [],
				'donationReceipt' => true,
				'exportState' => AddressChange::EXPORT_STATE_NO_DATA
			];

			$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testGetWithMissingToken_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$client->jsonRequest(
				Request::METHOD_GET,
				'/api/v1/address_change'
			);

			$response = $client->getResponse();

			$this->assertSame( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
			$this->assertJsonResponse( [
				'ERR' => 'No route found for "GET http://localhost/api/v1/address_change"'
			], $response );
		} );
	}

	public function testGetWithInvalidIdentifier_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$client->jsonRequest(
				Request::METHOD_GET,
				'/api/v1/address_change/NOT AN EXISTING UUID'
			);

			$response = $client->getResponse();

			$this->assertSame( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
			$this->assertJsonResponse( [
				'ERR' => 'address_change_token_not_found',
				'errors' => []
			], $response );
		} );
	}

	public function testPutWithMissingToken_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change',
			);

			$response = $client->getResponse();

			$this->assertJsonResponse( [
				'ERR' => 'No route found for "PUT http://localhost/api/v1/address_change"'
			], $response );
		} );
	}

	public function testPutWithInvalidIdentifier_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/NOT AN EXISTING UUID',
				[ 'addressType' => 'person' ]
			);

			$response = $client->getResponse();

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
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();
			$factory->getEntityManager()->persist( $addressChange );
			$factory->getEntityManager()->flush();

			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/' . $addressChange->getCurrentIdentifier()->__toString(),
				[]
			);

			$response = $client->getResponse();

			$this->assertJsonResponse( [
				'ERR' => 'address_change_empty_request_body',
				'errors' => []
			], $response );
		} );
	}

	public function testPutWithInvalidData_errorIsReturned(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$factory->getEntityManager()->persist( $addressChange );
			$factory->getEntityManager()->flush();

			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/' . $addressChange->getCurrentIdentifier()->__toString(),
				[ 'notAField' => 'not a value' ]
			);

			$response = $client->getResponse();

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
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$factory->getEntityManager()->persist( $addressChange );
			$factory->getEntityManager()->flush();

			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/' . $addressChange->getCurrentIdentifier()->__toString(),
				[
					'addressType' => 'person',
					'firstName' => 'Graf',
					'lastName' => 'Zahl',
					'salutation' => 'Herr',
					'street' => 'Zählerweg 5',
					'postcode' => '12345',
					'city' => 'Berlin-Zehlendorf',
					'country' => 'DE'
				]
			);

			$expectedResponse = [
				'identifier' => $addressChange->getCurrentIdentifier()->__toString(),
				'previousIdentifier' => $addressChange->getPreviousIdentifier()->__toString(),
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

			$response = $client->getResponse();

			$this->assertJsonResponse( $expectedResponse, $response );
		} );
	}

	public function testPutWithValidData_userIsOptedIntoReceiptByDefault(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$entityManager = $factory->getEntityManager();
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$entityManager->persist( $addressChange );
			$entityManager->flush();

			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/' . $addressChange->getCurrentIdentifier()->__toString(),
				[
					'addressType' => 'person',
					'firstName' => 'Graf',
					'lastName' => 'Zahl',
					'salutation' => 'Herr',
					'street' => 'Zählerweg 5',
					'postcode' => '12345',
					'city' => 'Berlin-Zehlendorf',
					'country' => 'DE'
				]
			);

			$entityManager->clear( AddressChange::class );
			$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find( $addressChange->getId() );
			$this->assertTrue( $addressChangeAfterRequest->isOptedIntoDonationReceipt(), 'Donor should be opted into donation receipt' );
		} );
	}

	public function testPutWithValidData_userCanOptOutOfReceiptWhileStillProvidingAnAddress(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$entityManager = $factory->getEntityManager();
			$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

			$entityManager->persist( $addressChange );
			$entityManager->flush();

			$client->jsonRequest(
				Request::METHOD_PUT,
				'/api/v1/address_change/' . $addressChange->getCurrentIdentifier()->__toString(),
				[
					'addressType' => 'person',
					'firstName' => 'Graf',
					'lastName' => 'Zahl',
					'salutation' => 'Herr',
					'street' => 'Zählerweg 5',
					'postcode' => '12345',
					'city' => 'Berlin-Zehlendorf',
					'country' => 'DE',
					'receiptOptOut' => true
				]
			);

			$entityManager->clear( AddressChange::class );
			$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find( $addressChange->getId() );
			$this->assertFalse( $addressChangeAfterRequest->isOptedIntoDonationReceipt(), 'Donor should be opted out of donation receipt' );
		} );
	}
}
