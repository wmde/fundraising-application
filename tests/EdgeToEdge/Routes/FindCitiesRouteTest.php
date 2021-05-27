<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidLocation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Validation\FindCitiesController
 * @covers \WMDE\Fundraising\Frontend\Autocomplete\UseCases\FindCitiesUseCase
 */
class FindCitiesRouteTest extends WebRouteTestCase {

	public function testGivenValidPostcode_endpointReturnsCities(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( false );

			$this->insertLocationForPostcodeAndCity( $factory, '12345', 'Wexford' );
			$this->insertLocationForPostcodeAndCity( $factory, '12345', 'Waterford' );
			$this->insertLocationForPostcodeAndCity( $factory, '34567', 'Kildare' );
			$this->insertLocationForPostcodeAndCity( $factory, '12345', 'Wicklow' );

			$client->request(
				'POST',
				'/api/v1/cities.json',
				[ 'postcode' => '12345' ]
			);

			$response = $client->getResponse();

			$this->assertJsonSuccessResponse( [ 'Wexford', 'Waterford', 'Wicklow' ], $response );
		} );
	}

	private function insertLocationForPostcodeAndCity( FunFunFactory $factory, string $postcode, string $city ): void {
		$location = ValidLocation::validLocationForPostcodeAndCity( $postcode, $city );
		$entityManager = $factory->getEntityManager();

		$entityManager->persist( $location );
		$entityManager->flush();
	}
}
