<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

class ValidateAmountRouteTest extends WebRouteTestCase {

	private const PATH = '/validate-donation-amount';

	/**
	 * @dataProvider validHttpParametersProvider
	 */
	public function testGivenValidParameters_successResponseIsReturned( array $parameters ): void {
		$client = $this->createClient();
		$client->request( Request::METHOD_POST, self::PATH, $parameters );
		$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $client->getResponse() );
	}

	public function validHttpParametersProvider(): iterable {
		yield [ [ 'amount' => '1234' ] ];
	}

	/**
	 * @dataProvider invalidTestDataProvider
	 */
	public function testGivenInvalidParameters_matchingFailureResponseIsReturned( array $parameters, array $violations ): void {
		$client = $this->createClient();
		$client->request( Request::METHOD_POST, self::PATH, $parameters );
		$this->assertErrorJsonResponse( $client->getResponse() );
		$this->assertEquals( $violations, $this->getJsonFromResponse( $client->getResponse() )['messages'] );
	}

	public function invalidTestDataProvider(): iterable {
		yield [ [ 'amount' => '' ], [ 'amount' => 'Amount must be in cents.' ] ];
		yield [ [ 'amount' => 'fff' ],  [ 'amount' => 'Amount must be in cents.' ] ];
		yield [ [ 'amount' => '12.34' ],  [ 'amount' => 'Amount must be in cents.' ] ];
		yield [ [ 'amount' => '1233.99' ], [ 'amount' => 'Amount must be in cents.' ] ];
		yield [ [ 'amount' => '12,34' ], [ 'amount' => 'Amount must be in cents.' ] ];
		yield [ [ 'amount' => '12' ], [ 'amount' => 'Amount too low' ] ];
		yield [ [ 'amount' => '12879342897234879234' ], [ 'amount' => 'Amount too high' ] ];
		yield [ [ ], [ 'amount' => 'Amount must be in cents.' ] ];
	}
}
