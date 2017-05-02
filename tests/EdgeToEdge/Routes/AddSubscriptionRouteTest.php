<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;
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

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
	}

	public function testValidSubscriptionRequestGetsPersisted() {

		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$subscriptionRepository = new SubscriptionRepositorySpy();
				$factory->setSubscriptionRepository( $subscriptionRepository );

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
		);

	}

	public function testGivenValidDataAndNoContentType_routeReturnsRedirectToSucccessPage() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

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
		);
	}

	public function testGivenInvalidDataAndNoContentType_routeDisplaysFormPage() {

		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$client->request(
					'POST',
					'/contact/subscribe',
					$this->invalidFormInput
				);

				$response = $client->getResponse();
				$content = $response->getContent();
				preg_match_all( '/<span class=\"form-error\">(\w+)<\/span>/s', $content, $errorMatches );

				$errorMatches = $errorMatches[1];

				$this->assertContains( 'text/html', $response->headers->get( 'Content-Type' ) );
				$this->assertCount( 1, $errorMatches, 'No error count found in test template' );
				$this->assertRegExp( '/id="first-name" value="Nyan"/', $content );
			}
		);
	}

	public function testGivenInvalidDataAndJSONContentType_routeReturnsSuccessResult() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

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
		);
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
		$this->assertSame( 'email_address_wrong_format', $responseData['errors']['email'] );
	}

	public function testGivenValidDataAndJSONPRequest_routeReturnsResult() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

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
		);
	}

	public function testGivenDataNeedingModerationAndNoContentType_routeReturnsRedirectToModerationPage() {
		$config = [ 'text-policies' => [ 'fields' => [ 'badwords' => 'tests/templates/Banned_Cats.txt' ] ] ];
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
