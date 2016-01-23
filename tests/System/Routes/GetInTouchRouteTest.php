<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Swift_NullTransport;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchResponse;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		$factory->setMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$factory->getOperatorAddress()
		) );
	}

	public function testGivenValidRequest_contactRequestIsProperlyProcessed() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'Vorname' => 'Curious',
				'Nachname' => 'Guy',
				'email' => 'curious.guy@gmail.com',
				'Betreff' => 'What is it you are doing?!',
				'kommentar' => 'Just tell me'
			]
		);

		$this->assertContains(
			'request successful',
			$client->getResponse()->getContent()
		);
	}

	public function testGivenInValidRequest_validationFails() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[
				'Vorname' => 'Curious',
				'Nachname' => 'Guy',
				'email' => 'curious.guy@gmail',
				'Betreff' => 'What is it you are doing?!',
				'kommentar' => 'Just tell me'
			]
		);

		$this->assertContains(
			'validation failed',
			$client->getResponse()->getContent()
		);
	}

}
