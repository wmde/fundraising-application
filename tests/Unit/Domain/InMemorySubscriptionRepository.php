<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\InMemorySubscriptionRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class InMemorySubscriptionRepositoryTest  extends \PHPUnit_Framework_TestCase {

	public function testWhenRepositoryIsInitialized_ItContainsNoSubscriptions() {
		$repo = new InMemorySubscriptionRepository( [] );
		$this->assertEquals( [], $repo->getSubscriptions() );
	}

	public function testSubscriptionsAreStored() {
		$subscription = $this->getMock( Subscription::class );
		$repo = new InMemorySubscriptionRepository( [] );
		$repo->storeSubscription( $subscription );
		$this->assertEquals( [ $subscription ], $repo->getSubscriptions() );
	}

}