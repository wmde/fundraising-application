<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Validation\FindCitiesController;
use WMDE\Fundraising\Frontend\Autocomplete\UseCases\FindCitiesUseCase;
use WMDE\Fundraising\Frontend\Tests\Data\ValidLocation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( FindCitiesController::class )]
#[CoversClass( FindCitiesUseCase::class )]
class FindCitiesRouteTest extends WebRouteTestCase {

	public function testGivenValidPostcode_endpointReturnsCities(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$client->followRedirects( false );

		$entityManager = $factory->getEntityManager();

		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Sesame' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '34567', 'Kildare', 'Sesame' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wicklow', 'Sesame' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Großröhrsdorf', 'Sesame' ) );

		$entityManager->flush();

		$client->request(
			'POST',
			'/api/v1/cities.json',
			[ 'postcode' => '12345' ]
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'Großröhrsdorf', 'Waterford', 'Wexford', 'Wicklow' ], $response );
	}

	public function testGivenPostcodeWithNonNumericCharacters_theyGetSanitized(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$client->followRedirects( false );

		$entityManager = $factory->getEntityManager();
		$entityManager->persist( ValidLocation::newValidLocation( '54321', 'Wexford', 'Sesame' ) );
		$entityManager->flush();

		$client->request(
			'POST',
			'/api/v1/cities.json',
			[ 'postcode' => ' 54 Haha alle Zeichen weg!!! 321 '
			]
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'Wexford' ], $response );
	}

}
