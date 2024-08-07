<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Validation\FindStreetsController;
use WMDE\Fundraising\Frontend\Autocomplete\UseCases\FindStreetsUseCase;
use WMDE\Fundraising\Frontend\Tests\Data\ValidLocation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( FindStreetsController::class )]
#[CoversClass( FindStreetsUseCase::class )]
class FindStreetsRouteTest extends WebRouteTestCase {

	public function testGivenValidPostcode_endpointReturnsStreets(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$client->followRedirects( false );

		$entityManager = $factory->getEntityManager();

		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Elm' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '34567', 'Kildare', 'Crime Alley' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wicklow', 'Respectable' ) );
		$entityManager->persist( ValidLocation::newValidLocation( '12345', 'Großröhrsdorf', 'Straße Eins' ) );

		$entityManager->flush();

		$client->request(
			'POST',
			'/api/v1/streets.json',
			[ 'postcode' => '12345' ]
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'Elm', 'Respectable', 'Sesame', 'Straße Eins' ], $response );
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
			'/api/v1/streets.json',
			[ 'postcode' => ' 54 Haha alle Zeichen weg!!! 321 '
			]
		);

		$response = $client->getResponse();

		$this->assertJsonSuccessResponse( [ 'Sesame' ], $response );
	}

}
