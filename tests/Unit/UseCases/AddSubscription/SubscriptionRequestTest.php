<?php


namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\AddSubscription;

use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionRequestTest extends \PHPUnit_Framework_TestCase {
	public function testSubscriptionRequestCanBeInitializedFromArray() {
		$subscriptionRequest = SubscriptionRequest::createFromArray( [
			'firstName' => 'Nyan',
			'lastName' => 'Cat',
			'salutation' => 'Herr',
			'title' => 'Prof. Dr. Dr.',
			'address' => 'Awesome Way 1',
			'city' => 'Berlin',
			'postcode' => '12345',
			'email' => 'me@nyancat.wtf',
			'wikilogin' => true
		] );
		$this->assertEquals( $subscriptionRequest->getFirstName(), 'Nyan' );
		$this->assertEquals( $subscriptionRequest->getLastName(), 'Cat' );
		$this->assertEquals( $subscriptionRequest->getSalutation(), 'Herr' );
		$this->assertEquals( $subscriptionRequest->getTitle(), 'Prof. Dr. Dr.' );
		$this->assertEquals( $subscriptionRequest->getAddress(), 'Awesome Way 1' );
		$this->assertEquals( $subscriptionRequest->getCity(), 'Berlin' );
		$this->assertEquals( $subscriptionRequest->getPostcode(), '12345' );
		$this->assertEquals( $subscriptionRequest->getEmail(), 'me@nyancat.wtf' );
		$this->assertEquals( $subscriptionRequest->getWikilogin(), true );
	}

	public function testGivenInvalidValues_WikiloginIsFalse() {
		$request = new SubscriptionRequest();
		$request->setWikiloginFromValues( ['', 'foo', 'bar' ] );
		$this->assertFalse( $request->getWikilogin() );
	}

	public function testGivenValues_WikiloginChoosesTheFirstValidValue() {
		$request = new SubscriptionRequest();
		$request->setWikiloginFromValues( ['', 'yes' ] );
		$this->assertTrue( $request->getWikilogin() );

		$request->setWikiloginFromValues( ['0', 'yes' ] );
		$this->assertFalse( $request->getWikilogin() );
	}

}
