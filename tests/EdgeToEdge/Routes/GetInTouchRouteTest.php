<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$factory->setNullMessenger();
	}

	public function testGivenValidRequest_contactRequestIsProperlyProcessed() {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'firstname' => 'Curious',
				'lastname' => 'Guy',
				'email' => 'curious.guy@gmail.com',
				'subject' => 'What is it you are doing?!',
				'messageBody' => 'Just tell me'
			]
		);
		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect(), 'Is redirect response' );
		$this->assertSame( '/page/KontaktBestaetigung', $response->headers->get( 'Location' ) );
	}

	public function testGivenInvalidRequest_validationFails() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'firstname' => 'Curious',
				'lastname' => 'Guy',
				'email' => 'curious.guy@gmail',
				'subject' => 'What is it you are doing?!',
				'messageBody' => 'Just tell me'
			]
		);

		$response = $client->getResponse();
		$contentType = $response->headers->get( 'Content-Type' );
		$content = $response->getContent();
		$errorsFound = preg_match( '/Errors: (\\d+)/s', $content, $errorMatches );

		$this->assertContains( 'text/html', $contentType, 'Wrong content type: ' . $contentType );
		$this->assertSame( 1, $errorsFound, 'No error count found in test template' );
		$this->assertSame( 1, $errorsFound, 'No error count found in test template' );
		$this->assertGreaterThan( 0, (int) $errorMatches[1], 'Error list was empty' );
		$this->assertContains( 'First Name: Curious', $content );
	}

	public function testOnException_errorPageIsRendered() {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ) {
				$messenger = $this->getMockBuilder( Messenger::class )
					->disableOriginalConstructor()
					->getMock();

				$messenger->expects( $this->any() )
					->method( 'sendMessageToUser' )
					->willThrowException( new \RuntimeException( 'Something unexpected happened' ) );

				$factory->setMessenger( $messenger );
			},
			self::DISABLE_DEBUG
		);

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'firstname' => 'Some Other',
				'lastname' => 'Guy',
				'email' => 'someother@alltheguys.com',
				'subject' => 'Give me an error page',
				'messageBody' => 'Let me see if I can raise an exception'
			]
		);

		$response = $client->getResponse();
		$content = $response->getContent();

		$this->assertContains( 'Internal Error: Something unexpected happened', $content );
	}
}
