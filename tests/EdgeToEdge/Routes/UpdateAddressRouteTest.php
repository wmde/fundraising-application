<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\Entities\AddressChange;
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
			[ 'skin' => 'laika' ],
			function ( Client $client, FunFunFactory $factory ): void {

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
			[ 'skin' => 'laika' ],
			function ( Client $client, FunFunFactory $factory ): void {
				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();
				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();
				$addressToken = $donation->getAddressChange()->getCurrentIdentifier();

				$this->doRequestWithValidData( $client, $addressToken );
				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();

				$this->assertTrue( $response->isOk() );
				$this->assertNotTrue( isset( $dataVars->message ), 'No error message is sent.' );
			}
		);
	}

	private function doRequestWithValidData( Client $client, string $addressToken, array $additionalData = [] ): void {
		$client->request(
			Request::METHOD_POST,
			self::PATH . '?addressToken=' . $addressToken,
			array_merge(
				[
					'addressType' => 'person',
					'firstName' => 'Graf',
					'lastName' => 'Zahl',
					'salutation' => 'Herr',
					'street' => 'Zählerweg 5',
					'postcode' => '12345',
					'city' => 'Berlin-Zehlendorf',
					'country' => 'DE'
				],
				$additionalData
			)
		);
	}

	public function testUsersOptIntoReceiptByDefault(): void {
		$this->createEnvironment(
			[ 'skin' => 'laika' ],
			function ( Client $client, FunFunFactory $factory ): void {
				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();
				$entityManager = $factory->getEntityManager();
				$entityManager->persist( $donation );
				$entityManager->flush();
				$generatedAddressChange = $donation->getAddressChange();
				$addressToken = $generatedAddressChange->getCurrentIdentifier();
				// Doctrine Entity has different accessors, use Reflection as a crutch
				// until we can use the Domain Entity AddressChange and use getId()
				// see https://phabricator.wikimedia.org/T232010
				$addressChangeId = $this->getDoctrineAddressChangeId( $generatedAddressChange );

				$this->doRequestWithValidData( $client, $addressToken );

				$entityManager->clear( AddressChange::class );
				$addressChange = $entityManager->getRepository( AddressChange::class )->find( $addressChangeId );
				$this->assertTrue( $addressChange->isOptedIntoDonationReceipt(), 'Donor should be opted into donation receipt' );
			}
		);
	}

	private function getDoctrineAddressChangeId( AddressChange $addressChange ): int {
		$prop = ( new \ReflectionClass( AddressChange::class ) )->getProperty( 'id' );
		$prop->setAccessible( true );
		return $prop->getValue( $addressChange );
	}

	public function testUsersCanOptOutOfReceiptWhileStillProvidingAnAddress(): void {
		$this->createEnvironment(
			[ 'skin' => 'laika' ],
			function ( Client $client, FunFunFactory $factory ): void {
				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();
				$entityManager = $factory->getEntityManager();
				$entityManager->persist( $donation );
				$entityManager->flush();
				$generatedAddressChange = $donation->getAddressChange();
				$addressToken = $generatedAddressChange->getCurrentIdentifier();
				// Doctrine Entity has different accessors, use Reflection as a crutch
				// until we can use the Domain Entity AddressChange and use getId()
				// see https://phabricator.wikimedia.org/T232010
				$addressChangeId = $this->getDoctrineAddressChangeId( $generatedAddressChange );

				$this->doRequestWithValidData( $client, $addressToken, [ 'receiptOptOut' => '1' ] );

				$entityManager->clear( AddressChange::class );
				$addressChange = $entityManager->getRepository( AddressChange::class )->find( $addressChangeId );
				$this->assertFalse( $addressChange->isOptedIntoDonationReceipt(), 'Donor should be opted out of donation receipt' );
			}
		);
	}

	public function testOptingOutWithEmptyFields_serverShowsAConfirmationPage(): void {
		$this->createEnvironment(
			[ 'skin' => 'laika' ],
			function ( Client $client, FunFunFactory $factory ): void {
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

	private function getDataApplicationVars( Crawler $crawler ): object {
		return json_decode( $crawler->filter( '#app' )->getNode( 0 )->getAttribute( 'data-application-vars' ) );
	}
}
