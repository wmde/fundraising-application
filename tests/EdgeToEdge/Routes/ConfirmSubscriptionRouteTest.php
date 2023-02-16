<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\SubscriptionContext\Domain\Model\Subscription;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Subscription\ConfirmSubscriptionController
 */
class ConfirmSubscriptionRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	public function testGivenAnUnconfirmedSubscriptionRequest_successPageIsDisplayed(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$subscription = new Subscription();
		$subscription->setConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'tester@example.com' );

		$factory->getSubscriptionRepository()->storeSubscription( $subscription );

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertTrue( $response->isOk() );
		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertFalse( property_exists( $dataVars, 'error_message' ), 'JSON result should not contain an error message' );
	}

	public function testGivenANonHexadecimalConfirmationCode_confirmationPageIsNotFound(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'GET',
			'/contact/confirm-subscription/kittens'
		);

		$this->assert404( $client->getResponse() );
	}

	public function testGivenNoSubscription_anErrorIsDisplayed(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertSame( 200, $response->getStatusCode() );

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertSame( 'subscription_confirmation_code_not_found', $dataVars->error_message );
	}

	public function testGivenAConfirmedSubscriptionRequest_successPageIsDisplayed(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$subscription = new Subscription();
		$subscription->setConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'tester@example.com' );
		$subscription->markAsConfirmed();

		$factory->getSubscriptionRepository()->storeSubscription( $subscription );

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertSame( 200, $response->getStatusCode() );
		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertSame( 'subscription_already_confirmed', $dataVars->error_message );
	}
}
