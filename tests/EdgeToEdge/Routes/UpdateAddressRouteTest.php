<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @license GNU GPL v2+
 */
class UpdateAddressRouteTest extends WebRouteTestCase {

	private const PATH = 'update-address';

	public function testWhenInvalidDataIsSent_serverThrowsAnError(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();

				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '?addressToken=' . $addressToken,
					[  ]
				);

				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertSame( 'Invalid value for field "Company".', $dataVars->message );
			}
		);
	}

	public function testWhenValidDataIsSent_serverShowsAConfirmationPage(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();

				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '?addressToken=' . $addressToken,
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
				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertNotTrue( isset( $dataVars->message ), 'No error message is sent.' );
			}
		);
	}

	public function testOptingOutWithEmptyFields_serverShowsAConfirmationPage(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();

				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '?addressToken=' . $addressToken,
					[
						'receiptOptOut' => '1'
					]
				);

				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertNotTrue( isset( $dataVars->message ), 'No error message is sent.' );
			}
		);
	}

	private function setDefaultSkin( FunFunFactory $factory, string $skinName ): void {
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				[ 'skins' => [ 'default_bucket' => $skinName ] ]
			)
		);
	}

	private function getDataApplicationVars( Crawler $crawler ): object {
		return json_decode( $crawler->filter( '#app' )->getNode( 0 )->getAttribute( 'data-application-vars' ) );
	}
}
