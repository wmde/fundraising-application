<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\StaticContent\ContactRequestController;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( ContactRequestController::class )]
class GetInTouchRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	public function testGivenValidRequest_contactRequestIsProperlyProcessed(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'firstname' => 'Curious',
				'lastname' => 'Guy',
				'email' => 'curious.guy@gmail.com',
				'donationNumber' => '123456',
				'subject' => 'What is it you are doing?!',
				'category' => 'Other',
				'messageBody' => 'Just tell me'
			]
		);

		$this->assertResponseRedirects( '/page/Kontakt_Bestaetigung' );
	}

	public function testGivenInvalidRequest_validationFails(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

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

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertEquals( [
			'firstname' => 'Curious',
			'lastname' => 'Guy',
			'email' => 'no.email.format',
			'subject' => '',
			'messageBody' => '',
		], (array)$applicationVars->submitted_form_data );

		$this->assertEquals( [
			'subject' => 'field_required',
			'category' => 'field_required',
			'messageBody' => 'field_required',
			'email' => 'email_address_wrong_format',
		], (array)$applicationVars->errors );
	}

	public function testGivenGetRequest_formShownWithoutErrors(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$crawler = $client->request(
			'GET',
			'/contact/get-in-touch'
		);

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertStringContainsString( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) ?: '' );

		$this->assertArrayNotHasKey( 'errors', (array)$applicationVars );
	}

	public function testOnException_errorPageIsRendered(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$messenger = $this->getMockBuilder( Messenger::class )
			->disableOriginalConstructor()
			->getMock();

		$messenger->expects( $this->any() )
			->method( 'sendMessageToUser' )
			->willThrowException( new \RuntimeException( 'Something unexpected happened' ) );

		$this->getFactory()->setContactMessenger( $messenger );

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'firstname' => 'Some Other',
				'lastname' => 'Guy',
				'email' => 'someother@alltheguys.com',
				'donationNumber' => '123456',
				'subject' => 'Give me an error page',
				'category' => 'Other',
				'messageBody' => 'Let me see if I can raise an exception'
			]
		);

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 'Something unexpected happened', $applicationVars->message );
	}
}
