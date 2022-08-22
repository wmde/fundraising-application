<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ValidLocation;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Validation\FindCitiesController
 * @covers \WMDE\Fundraising\Frontend\Autocomplete\UseCases\FindCitiesUseCase
 */
class FindCitiesRouteTest extends WebRouteTestCase {

	public function testGivenValidPostcode_endpointReturnsCities(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( false );

			$entityManager = $factory->getEntityManager();

			$entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wexford' ) );
			$entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Waterford' ) );
			$entityManager->persist( ValidLocation::validLocationForCommunity( '34567', 'Kildare' ) );
			$entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wicklow' ) );
			$entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Großröhrsdorf' ) );

			$entityManager->flush();

			$client->request(
				'POST',
				'/api/v1/cities.json',
				[ 'postcode' => '12345' ]
			);

			$response = $client->getResponse();

			$this->assertJsonSuccessResponse( [ 'Großröhrsdorf', 'Waterford', 'Wexford', 'Wicklow' ], $response );
		} );
	}
}
