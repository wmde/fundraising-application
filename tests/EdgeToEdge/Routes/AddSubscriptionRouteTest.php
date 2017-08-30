<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\SubscriptionContext\Tests\Fixtures\SubscriptionRepositorySpy;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddSubscriptionRouteTest extends WebRouteTestCase {

	private $validFormInput = [
		'firstName' => 'Nyan',
		'lastName' => 'Cat',
		'salutation' => 'Herr',
		'title' => 'Prof. Dr.',
		'address' => 'Awesome Way 1',
		'city' => 'Berlin',
		'postcode' => '12345',
		'email' => 'jeroendedauw@gmail.com',
		'wikilogin' => true,
		'tracking' => 'test/blue',
		'source' => 'testCampaign',
	];

	private $invalidFormInput = [
		'firstName' => 'Nyan',
		'lastName' => '',
		// skip salutation and title since they won't be in the POST data if nothing is selected
		'address' => '',
		'city' => '',
		'postcode' => '',
		'email' => '',
		'wikilogin' => true
	];

	public function testValidSubscriptionRequestGetsPersisted(): void {

		$subscriptionRepository = new SubscriptionRepositorySpy();

		$client = $this->createClient( [], function ( FunFunFactory $factory ) use ( $subscriptionRepository ) {
			$factory->setSubscriptionRepository( $subscriptionRepository );
		} );

		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput
		);

		$this->assertCount( 1, $subscriptionRepository->getSubscriptions() );

		$subscription = $subscriptionRepository->getSubscriptions()[0];
		$address = $subscription->getAddress();

		$this->assertSame( 'Nyan', $address->getFirstName() );
		$this->assertSame( 'Cat', $address->getLastName() );
		$this->assertSame( 'Herr', $address->getSalutation() );
		$this->assertSame( 'Prof. Dr.', $address->getTitle() );
		$this->assertSame( 'Awesome Way 1', $address->getAddress() );
		$this->assertSame( 'Berlin', $address->getCity() );
		$this->assertSame( '12345', $address->getPostcode() );
		$this->assertSame( 'jeroendedauw@gmail.com', $subscription->getEmail() );
		$this->assertSame( 'test/blue', $subscription->getTracking() );
		$this->assertSame( 'testCampaign', $subscription->getSource() );

	}

	public function testGivenValidDataAndNoContentType_routeReturnsRedirectToSucccessPage(): void {
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput
		);
		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect(), 'Is redirect response' );
		$this->assertSame( '/page/Subscription_Success', $response->headers->get( 'Location' ) );
	}

	public function testGivenInvalidDataAndNoContentType_routeDisplaysFormPage(): void {
		$client = $this->createClient();

		$crawler = $client->request(
			'POST',
			'/contact/subscribe',
			$this->invalidFormInput
		);

		$this->assertContains( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) );

		$this->assertCount(
			1,
			$crawler->filter( 'span.form-error:contains("email_address_wrong_format")' )
		);

		$this->assertCount(
			1,
			$crawler->filter( 'input#first-name[value="Nyan"]' )
		);
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsSuccessResult(): void {
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput,
			[],
			['HTTP_ACCEPT' => 'application/json']
		);
		$response = $client->getResponse();
		$this->assertJsonSuccessResponse( ['status' => 'OK'], $response );
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsErrorResult(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->invalidFormInput,
			[],
			['HTTP_ACCEPT' => 'application/json']
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
		$client = $this->createClient();
		$client->request(
			'GET',
			'/contact/subscribe',
			array_merge(
				$this->validFormInput,
				['callback' => 'test']
			),
			[],
			['HTTP_ACCEPT' => 'application/javascript']
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertSame(
			file_get_contents( __DIR__ . '/../../Data/files/addSubscriptionResponse.js' ),
			$response->getContent()
		);
	}

	public function testGivenDataNeedingModerationAndNoContentType_routeReturnsRedirectToModerationPage(): void {
		$config = ['text-policies' => ['fields' => ['badwords' => 'tests/Data/files/Banned_Cats.txt']]];
		$client = $this->createClient( $config );
		$client->followRedirects( false );
		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput
		);
		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect(), 'Is redirect response' );
		$this->assertSame( '/page/Subscription_Moderation', $response->headers->get( 'Location' ) );
	}

}
