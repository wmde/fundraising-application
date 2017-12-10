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
		yield [ [ 'amount' => '' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ];
		yield [ [ 'amount' => 'fff' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ];
		yield [ [ 'amount' => '12.34' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be 100 or more.' ] ] ];
		yield [ [ 'amount' => '1233.99' ], [ 'amount' => [ 'This value should be of type digit.' ] ] ];
		yield [ [ 'amount' => '12,34' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ];
		yield [ [ 'amount' => '12' ], [ 'amount' => [ 'This value should be 100 or more.' ] ] ];
		yield [ [ 'amount' => '12879342897234879234' ], [ 'amount' => [ 'This value should be 10000000 or less.' ] ] ];
		yield [ [ 'amount' => '1234', 'something' => 'more' ], [ 'something' => [ 'This field was not expected.' ] ] ];
		yield [ [ 'no context' => 'indeed' ], [ 'amount' => [ 'This field is missing.' ], 'no context' => [ 'This field was not expected.' ] ] ];
	}
}
