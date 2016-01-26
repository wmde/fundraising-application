<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
use WMDE\Fundraising\Frontend\Tests\Fixtures\RequestRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddSubscriptionRouteTest extends WebRouteTestCase {

	private $validFormInput = [
		'firstName' => 'Nyan',
		'lastName' => 'Cat',
		'salutation' => 'Herr',
		'title' => 'Prof. Dr. Dr.',
		'address' => 'Awesome Way 1',
		'city' => 'Berlin',
		'postcode' => '12345',
		'email' => 'jeroendedauw@gmail.com',
		'wikilogin' => true
	];

	private $invalidFormInput = [
		'firstName' => 'Nyan',
		'lastName' => '',
		'salutation' => 'Herr',
		'title' => '',
		'address' => '',
		'city' => '',
		'postcode' => '',
		'email' => '',
		'wikilogin' => true
	];

	public function testValidSubscriptionRequestGetsPersisted() {
		$requestRepository = new RequestRepositorySpy();

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( $requestRepository ) {
			$factory->setRequestRepository( $requestRepository );
		} );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/subscribe',
			$this->validFormInput
		);

		$this->assertCount( 1, $requestRepository->getRequests() );

		$request = $requestRepository->getRequests()[0];

		$this->assertSame( 'Nyan', $request->getVorname() );
		$this->assertSame( 'Cat', $request->getNachname() );
		$this->assertSame( 'Herr', $request->getAnrede() );
		$this->assertSame( 'Prof. Dr. Dr.', $request->getTitel() );
		$this->assertSame( 'Awesome Way 1', $request->getStrasse() );
		$this->assertSame( 'Berlin', $request->getOrt() );
		$this->assertSame( '12345', $request->getPlz() );
		$this->assertSame( 'jeroendedauw@gmail.com', $request->getEmail() );
		$this->assertTrue( $request->getWikilogin() );
		$this->assertSame( Request::TYPE_SUBSCRIPTION, $request->getType() );
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
		$this->assertTrue($response->isRedirect(), 'Is redirect response' );
		$this->assertSame( '/page/SubscriptionSuccess', $response->headers->get( 'Location' ) );
	}

	public function testGivenInvalidDataAndNoContentType_routeDisplaysFormPage() {
		$client = $this->createClient( [], function ( FunFunFactory $factory, array $config ) {
			$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

			$api->expects( $this->any() )
				->method( 'postRequest' )
				->willReturnCallback( new ApiPostRequestHandler() );

			$factory->setMediaWikiApi( $api );
		} );

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
		$this->assertContains( 'FirstName: Nyan', $content );
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
	}

}
