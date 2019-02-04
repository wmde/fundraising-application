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
				$this->setDefaultSkin( $factory, 'cat17' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();

				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '/' . $addressToken,
					[  ]
				);

				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertSame( 1, $client->getCrawler()->filter( '.page-error' )->count() );
			}
		);
	}

	public function testWhenValidDataIsSent_serverShowsAConfirmationPage(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();

				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '/' . $addressToken,
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

				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertSame( 1, $client->getCrawler()->filter( '.page-address-update-success' )->count(), 'Confirmation page should be shown' );
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
}
