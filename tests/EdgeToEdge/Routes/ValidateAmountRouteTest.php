<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

class ValidateAmountRouteTest extends WebRouteTestCase {

	private const PATH = '/validate-donation-amount';

	/**
	 * @dataProvider getPassingTestData
	 */
	public function testGivenValidParameters_successResponseIsReturned( array $parameters ): void {
		$client = $this->createClient();
		$client->request( Request::METHOD_POST, self::PATH, $parameters );
		$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $client->getResponse() );
	}

	public function getPassingTestData(): array {
		return [
			[ [ 'amount' => '1234' ] ],
		];
	}

	/**
	 * @dataProvider getFailingTestData
	 */
	public function testGivenInvalidParameters_matchingFailureResponseIsReturned( array $parameters, array $violations ): void {
		$client = $this->createClient();
		$client->request( Request::METHOD_POST, self::PATH, $parameters );
		$this->assertErrorJsonResponse( $client->getResponse() );
		$this->assertEquals( $violations, $this->getJsonFromResponse( $client->getResponse() )['messages'] );
	}

	public function getFailingTestData(): array {
		return [
			[ [ 'amount' => '' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ],
			[ [ 'amount' => 'fff' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ],
			[ [ 'amount' => '12.34' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be 100 or more.' ] ] ],
			[ [ 'amount' => '1233.99' ], [ 'amount' => [ 'This value should be of type digit.' ] ] ],
			[ [ 'amount' => '12,34' ], [ 'amount' => [ 'This value should be of type digit.', 'This value should be a valid number.' ] ] ],
			[ [ 'amount' => '12' ], [ 'amount' => [ 'This value should be 100 or more.' ] ] ],
			[ [ 'amount' => '12879342897234879234' ], [ 'amount' => [ 'This value should be 10000000 or less.' ] ] ],
			[ [ 'amount' => '1234', 'something' => 'more' ], [ 'something' => [ 'This field was not expected.' ] ] ],
			[ [ 'no context' => 'indeed' ], [ 'amount' => [ 'This field is missing.' ], 'no context' => [ 'This field was not expected.' ] ] ]
		];
	}
}
