<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingEmailValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	public function testGivenValidRequest_contactRequestIsProperlyProcessed(): void {
		$client = $this->createClient( [], function ( FunFunFactory $factory ): void {
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

	public function testGivenInvalidRequest_validationFails(): void {

		$client = $this->createClient();

		$crawler = $client->request(
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

		$this->assertContains( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) );

		$this->assertCount(
			3,
			$crawler->filter( 'span.form-error' )
		);
		$this->assertCount(
			1,
			$crawler->filter( 'input[name="firstname"][value="Curious"]' )
		);
		$this->assertCount(
			1,
			$crawler->filter( 'input[name="lastname"][value="Guy"]' )
		);
	}

	public function testGivenGetRequest_formShownWithoutErrors(): void {

		$client = $this->createClient();

		$crawler = $client->request(
			'GET',
			'/contact/get-in-touch'
		);

		$this->assertContains( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) );
		$this->assertCount(
			0,
			$crawler->filter( 'span.form-error' )
		);
	}

	public function testOnException_errorPageIsRendered(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
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
