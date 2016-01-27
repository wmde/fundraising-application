<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Swift_NullTransport;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$factory->setMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$factory->getOperatorAddress() )
		);
	}

	public function testGivenValidRequest_contactRequestIsProperlyProcessed() {
		$client = $this->createClient();

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
				'firstname' => 'Curious',
				'lastname' => 'Guy',
				'email' => 'curious.guy@gmail',
				'subject' => 'What is it you are doing?!',
				'messageBody' => 'Just tell me'
			]
		);

		$this->assertContains(
			'validation failed',
			$client->getResponse()->getContent()
		);
	}

}
