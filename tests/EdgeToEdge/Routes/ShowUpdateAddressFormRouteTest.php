<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
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
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

				$factory->getEntityManager()->persist( $addressChange );
				$factory->getEntityManager()->flush();

				$this->performRequest(
					$client,
					$addressChange->getCurrentIdentifier()->__toString()
				);

				$response = $client->getResponse();

				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$this->assertTrue( $response->isOk() );
				$this->assertSame( $addressChange->getCurrentIdentifier()->__toString(), $dataVars->addressToken );
			}
		);
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
