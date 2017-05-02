<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingEmailValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
	}

	public function testGivenValidRequest_contactRequestIsProperlyProcessed() {
		$client = $this->createClient( [], function ( FunFunFactory $factory ) {
			$factory->setEmailValidator( new SucceedingEmailValidator() );
		} );

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
		$this->assertSame( '/page/Kontakt_Bestaetigung', $response->headers->get( 'Location' ) );
	}

	public function testGivenInvalidRequest_validationFails() {

		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$client->request(
					'POST',
					'/contact/get-in-touch',
					[
						'firstname' => 'Curious',
						'lastname' => 'Guy',
						'email' => 'no.email.format',
						'subject' => '',
						'messageBody' => ''
					]
				);

				$response = $client->getResponse();
				$content = $response->getContent();
				preg_match_all( '/<span class=\"form-error\">(\w+)<\/span>/s', $content, $errorMatches );

				$errorMatches = $errorMatches[1];

				$this->assertContains( 'text/html', $response->headers->get( 'Content-Type' ) );
				$this->assertCount( 3, $errorMatches, 'No error count found in test template' );
				$this->assertRegExp( '/<input .+? name="firstname" .+? value="Curious"/', $content );
				$this->assertRegExp( '/<input .+? name="lastname" .+? value="Guy"/', $content );
			}
		);
	}

	public function testGivenGetRequest_formShownWithoutErrors() {

		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$client->request(
					'GET',
					'/contact/get-in-touch'
				);

				$response = $client->getResponse();

				$this->assertContains( 'text/html', $response->headers->get( 'Content-Type' ) );
				$this->assertNotRegExp( '/<span class=\"form-error\">(\w+)<\/span>/s', $response->getContent() );
			}
		);
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

				$factory->setSuborganizationMessenger( $messenger );
				$factory->setEmailValidator( new SucceedingEmailValidator() );
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
