<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Tests\Fixtures\RequestRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddSubscriptionRouteTest extends SystemTestCase {

	public function testValidSubscriptionRequestGetsPersisted() {
		$requestRepository = new RequestRepositorySpy();

		$this->testEnvironment->getFactory()->setRequestRepository( $requestRepository );

		$client = $this->createClient();
		$client->request(
			'POST',
			'/contact/subscribe',
			[
				'firstName' => 'Nyan',
				'lastName' => 'Cat',
				'salutation' => 'Herr',
				'title' => 'Prof. Dr. Dr.',
				'address' => 'Awesome Way 1',
				'city' => 'Berlin',
				'postcode' => '12345',
				'email' => 'jeroendedauw@gmail.com',
				'wikilogin' => true
			]
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

}
