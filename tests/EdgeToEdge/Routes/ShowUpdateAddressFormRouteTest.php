<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\AddressChange\ShowUpdateAddressController
 */
class ShowUpdateAddressFormRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const PATH = 'update-address';
	private const DUMMY_DONATION_ID = 0;

	private const INVALID_TOKEN = 'abcdefghijklmnopqrstuvwxzy12345';

	public function testWhenCorrectUpdateAddressTokenIsSupplied_addressChangeFormIsShown(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

		$factory->getEntityManager()->persist( $addressChange );
		$factory->getEntityManager()->flush();

		$this->performRequest(
			$client,
			$addressChange->getCurrentIdentifier()->__toString()
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isOk(), 'Response should be 200 OK' );
		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertSame( $addressChange->getCurrentIdentifier()->__toString(), $dataVars->addressToken );
	}

	public function testWhenPreviousUpdateAddressTokenIsSupplied_redirectsToAlreadyChangedPage(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();
		// new address changes have the same "current" and "previous" ID, opting out creates a new one
		$addressChange->optOutOfDonationReceipt( AddressChangeId::fromString( AddressChangeBuilder::generateUuid() ) );
		$factory->getEntityManager()->persist( $addressChange );
		$factory->getEntityManager()->flush();
		$addressToken = $addressChange->getPreviousIdentifier()->__toString();
		$expectedRedirectUrl = $factory->getUrlGenerator()->generateAbsoluteUrl(
			Routes::UPDATE_ADDRESS_ALREADY_UPDATED,
			[ 'addressToken' => $addressToken ]
		);

		$client->followRedirects( false );
		$this->performRequest( $client, $addressToken );

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirection(), 'Response should be a redirection' );
		$this->assertStringStartsWith( $expectedRedirectUrl, $response->headers->get( 'Location' ), "Response should redirect to $expectedRedirectUrl" );
	}

	public function testWhenIncorrectUpdateAddressTokenIsSupplied_accessToAddressChangeFormIsDenied(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$this->performRequest(
					$client,
					self::INVALID_TOKEN
				);

				$response = $client->getResponse();
				$this->assertTrue( $response->isForbidden() );
			}
		);
	}

	private function performRequest( Client $client, string $addressToken ): Crawler {
		return $client->request(
			Request::METHOD_GET,
			self::PATH . '?addressToken=' . $addressToken,
			[ 'updateToken' => $addressToken ]
		);
	}

}
