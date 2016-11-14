<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
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
		'wikilogin' => true
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

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
	}

	public function testValidSubscriptionRequestGetsPersisted() {
		$subscriptionRepository = new SubscriptionRepositorySpy();

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( $subscriptionRepository ) {
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
	}

	public function testGivenValidDataAndNoContentType_routeReturnsRedirectToSucccessPage() {
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

	public function testGivenInvalidDataAndNoContentType_routeDisplaysFormPage() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->invalidFormInput
		);

		$response = $client->getResponse();
		$contentType = $response->headers->get( 'Content-Type' );
		$content = $response->getContent();
		$errorsFound = preg_match( '/Errors: (\\d+)/s', $content, $errorMatches );

		$this->assertContains( 'text/html', $contentType, 'Wrong content type: ' . $contentType );
		$this->assertSame( 1, $errorsFound, 'No error count found in test template' );
		$this->assertGreaterThan( 0, (int) $errorMatches[1], 'Error list was empty' );
		$this->assertContains( 'First Name: Nyan', $content );
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsSuccessResult() {
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
		$this->assertJsonSuccessResponse( ['status' => 'OK'], $response );
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsErrorResult() {
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
		$this->assertSame( 'Dieses Feld ist ein Pflichtfeld', $responseData['errors']['lastName'] );
	}

	public function testGivenDataNeedingModerationAndNoContentType_routeReturnsRedirectToModerationPage() {
		$config = [ 'text-policies' => [ 'fields' => [ 'badwords' => 'No_Cats' ] ] ];
		$client = $this->createClient( $config, function( FunFunFactory $factory ) {
			$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();
			$api->expects( $this->any() )
				->method( 'postRequest' )
				->willReturnCallback( new ApiPostRequestHandler() );

			$factory->setMediaWikiApi( $api );
		} );
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
