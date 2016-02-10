<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SubscriptionRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Messenger;
use Swift_NullTransport;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionRouteTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$factory->setMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$factory->getOperatorAddress()
		) );
	}

	private function newSubscriptionAddress() {
		$address = new Address();
		$address->setSalutation( 'Herr' );
		$address->setFirstName( 'Nyan' );
		$address->setLastName( 'Cat' );
		$address->setTitle( 'Dr.' );
		return $address;
	}

	public function testGivenAnUnconfirmedSubscriptionRequest_successPageIsDisplayed() {
		$subscription = new Subscription();
		$subscription->setHexConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'tester@example.com' );
		$subscription->setAddress( $this->newSubscriptionAddress() );
		$subscription->setStatus( Subscription::STATUS_NEUTRAL );
		$subscriptionRepository = new SubscriptionRepositorySpy();
		$subscriptionRepository->storeSubscription( $subscription );

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( $subscriptionRepository ) {
			$factory->setSubscriptionRepository( $subscriptionRepository );
		} );

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertContains( 'Subscription confirmed.', $response->getContent() );
	}

	public function testGivenANonHexadecimalConfirmationCode_confirmationPageIsNotFound() {
		$client = $this->createClient( [], function( FunFunFactory $factory ) {
			$factory->setSubscriptionRepository( new SubscriptionRepositorySpy() );
		} );

		$client->request(
			'GET',
			'/contact/confirm-subscription/kittens'
		);

		$this->assert404( $client->getResponse() );
	}

	public function testGivenNoSubscription_AnErrorIsDisplayed() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertContains( 'Es konnte kein Eintrag mit diesem Bestätigungs-Code gefunden werden', $response->getContent() );
	}

	public function testGivenAConfirmedSubscriptionRequest_successPageIsDisplayed() {
		$subscription = new Subscription();
		$subscription->setHexConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'tester@example.com' );
		$subscription->setAddress( $this->newSubscriptionAddress() );
		$subscription->setStatus( Subscription::STATUS_CONFIRMED );
		$subscriptionRepository = new SubscriptionRepositorySpy();
		$subscriptionRepository->storeSubscription( $subscription );

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( $subscriptionRepository ) {
			$factory->setSubscriptionRepository( $subscriptionRepository );
		} );

		$client->request(
			'GET',
			'/contact/confirm-subscription/deadbeef'
		);
		$response = $client->getResponse();

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertContains( 'Diese E-Mail-Adresse wurde bereits bestätigt.', $response->getContent() );
	}
}
