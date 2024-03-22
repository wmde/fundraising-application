<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\SubscriptionContext\Tests\Fixtures\SubscriptionRepositorySpy;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Subscription\AddSubscriptionController
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionJsonPresenter
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\AddSubscriptionHtmlPresenter
 */
class AddSubscriptionRouteTest extends WebRouteTestCase {

	private array $validFormInput = [
		'email' => 'jeroendedauw@gmail.com',
		'wikilogin' => true,
		'source' => 'testCampaign',
	];

	private array $validFormInputWithSpaces = [
		'email' => "\tjeroendedauw@gmail.com   ",
		'wikilogin' => true,
		'source' => "\ntestCampaign\r\n",
	];

	private array $invalidFormInput = [
		'email' => 'not an email',
		'wikilogin' => true
	];

	public function testValidSubscriptionRequestGetsPersisted(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$subscriptionRepository = new SubscriptionRepositorySpy();
		$factory = $this->getFactory();
		$factory->setSubscriptionRepository( $subscriptionRepository );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/subscribe?piwik_campaign=test&piwik_kwd=blue',
			$this->validFormInput
		);

		$this->assertCount( 1, $subscriptionRepository->getSubscriptions() );

		$subscription = $subscriptionRepository->getSubscriptions()[0];

		$this->assertSame( 'jeroendedauw@gmail.com', $subscription->getEmail() );
		$this->assertSame( 'test/blue', $subscription->getTracking() );
		$this->assertSame( 'testCampaign', $subscription->getSource() );
	}

	public function testSubscriptionRequestWithLocaleSetsLocaleCookie(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$subscriptionRepository = new SubscriptionRepositorySpy();
		$factory = $this->getFactory();
		$factory->setSubscriptionRepository( $subscriptionRepository );

		$client->request(
			'POST',
			'/contact/subscribe?piwik_campaign=test&piwik_kwd=blue&locale=en_GB',
			$this->validFormInput
		);

		$this->assertSame( 'en_GB', $client->getCookieJar()->get( CookieNames::LOCALE )?->getValue() );
	}

	public function testLeadingAndTrailingWhitespaceGetsTrimmed(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$subscriptionRepository = new SubscriptionRepositorySpy();
		$factory = $this->getFactory();
		$factory->setSubscriptionRepository( $subscriptionRepository );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/subscribe?piwik_campaign=test&piwik_kwd=blue',
			$this->validFormInputWithSpaces
		);

		$this->assertCount( 1, $subscriptionRepository->getSubscriptions() );

		$subscription = $subscriptionRepository->getSubscriptions()[0];

		$this->assertSame( 'jeroendedauw@gmail.com', $subscription->getEmail() );
		$this->assertSame( 'test/blue', $subscription->getTracking() );
		$this->assertSame( 'testCampaign', $subscription->getSource() );
	}

	public function testGivenValidDataAndNoContentType_routeRedirectsToSuccessPage(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput
		);

		$this->assertResponseRedirects( '/page/Subscription_Success' );
	}

	public function testGivenInvalidDataAndNoContentType_routeRedirectsToSuccessPage(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$crawler = $client->request(
			'POST',
			'/contact/subscribe',
			$this->invalidFormInput
		);

		$this->assertResponseRedirects( '/page/Subscription_Success' );
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsSuccessResult(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput,
			[],
			[ 'HTTP_ACCEPT' => 'application/json' ]
		);
		$response = $client->getResponse();
		$this->assertJsonSuccessResponse( [ 'status' => 'OK' ], $response );
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsErrorResult(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->invalidFormInput,
			[],
			[ 'HTTP_ACCEPT' => 'application/json' ]
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertJson( $response->getContent(), 'response is json' );
		$responseData = json_decode( $response->getContent(), true );
		$this->assertSame( 'ERR', $responseData['status'] );
		$this->assertGreaterThan( 0, count( $responseData['errors'] ) );
		$this->assertSame( 'email_address_wrong_format', $responseData['errors']['email'] );
	}

	public function testGivenValidDataAndJSONPRequest_routeReturnsResult(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$client->request(
			'GET',
			'/contact/subscribe',
			array_merge(
				$this->validFormInput,
				[ 'callback' => 'test' ]
			),
			[],
			[ 'HTTP_ACCEPT' => 'application/javascript' ]
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertSame(
			file_get_contents( __DIR__ . '/../../Data/files/addSubscriptionResponse.js' ),
			$response->getContent()
		);
	}

	public function testGivenValidDataAndGetRequestWithoutJsonpIdentifier_routeReturnsBadRequest(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$client->request(
			'GET',
			'/contact/subscribe',
				$this->validFormInput
		);

		$response = $client->getResponse();
		$this->assertFalse( $response->isSuccessful() );
		$this->assertSame( 400, $response->getStatusCode(), 'Should return bad request' );
	}
}
