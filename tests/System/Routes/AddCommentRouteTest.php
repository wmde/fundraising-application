<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SubscriptionRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
use Swift_NullTransport;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentRouteTest extends WebRouteTestCase {

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'add-comment',
			[]
		);
	}

	public function testGivenRequestWithoutParameters_resultIsError() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'add-comment',
			[]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithoutTokens_resultIsError() {
		// TODO: insert donation

		$client = $this->createClient();

		$client->request(
			'POST',
			'add-comment',
			[
				'kommentar' => 'Your programmers deserve a raise',
				'public' => '1',
				'eintrag' => 'Uncle Bob',
				'sid' => '9001',
			]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithParameters_resultIsSuccess() {
		// TODO: insert donation
		self::markTestSkipped( 'Not implemented yet!' );

		$client = $this->createClient();

		$client->request(
			'POST',
			'add-comment',
			[
				'kommentar' => 'Your programmers deserve a raise',
				'public' => '1',
				'eintrag' => 'Uncle Bob',
				'token' => '1276888%2459b42194b31d0265df452735f6438a234bae2af7',
				'utoken' => 'b5b249c8beefb986faf8d186a3f16e86ef509ab2',
				'sid' => '9001',
			]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertSuccessJsonResponse( $response );
	}

}
