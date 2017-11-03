<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Tests\Unit\UseCases\AddSubscription;

use WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;

/**
 * @covers \WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionRequestTest extends \PHPUnit\Framework\TestCase {

	public function testGivenInvalidValues_WikiloginIsFalse(): void {
		$request = new SubscriptionRequest();
		$request->setWikiloginFromValues( ['', 'foo', 'bar' ] );
		$this->assertFalse( $request->getWikilogin() );
	}

	public function testGivenValues_WikiloginChoosesTheFirstValidValue(): void {
		$request = new SubscriptionRequest();
		$request->setWikiloginFromValues( ['', 'yes' ] );
		$this->assertTrue( $request->getWikilogin() );

		$request->setWikiloginFromValues( ['0', 'yes' ] );
		$this->assertFalse( $request->getWikilogin() );
	}

}
