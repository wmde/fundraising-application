<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipRouteTest extends WebRouteTestCase {

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'apply-for-membership',
			[]
		);
	}

	public function testGivenValidRequest_successResponseIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertSame( 'TODO success', $client->getResponse()->getContent() );
		} );
	}

	private function newValidHttpParameters(): array {
		return [
			'membership_type' => 'active',

			'adresstyp' => 'person',
			'anrede' => 'Herr',
			'titel' => '',
			'vorname' => 'blah',
			'nachname' => 'blub',
			'firma' => '',

			'strasse' => 'cnkevhwnw',
			'plz' => '11207',
			'ort' => 'Berlin',
			'country' => 'DE',

			'email' => 'gb@blah.com',
			'phone' => '1234555',
			'dob' => '30.02.9999',

			'membership_fee_interval' => '12',
			'membership_fee' => '25.00', // TODO: change to localized

			'bank_name' => 'ING-DiBa',
			'iban' => 'DE12500105170648489890',
			'bic' => 'INGDDEFFXXX',
			'account_number' => '0648489890',
			'bank_code' => '50010517',
		];
	}

	public function testGivenRequestWithInsufficientAmount_failureResponseIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$httpParameters = $this->newValidHttpParameters();
			$httpParameters['membership_fee'] = '1.00'; // TODO: change to localized

			$client->request(
				'POST',
				'apply-for-membership',
				$httpParameters
			);

			$this->assertSame( 'TODO fail', $client->getResponse()->getContent() );
		} );
	}

}
