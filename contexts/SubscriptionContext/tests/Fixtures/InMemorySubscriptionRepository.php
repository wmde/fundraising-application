<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Tests\Fixtures;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemorySubscriptionRepository implements SubscriptionRepository {

	/**
	 * @var Subscription[]
	 */
	private $subscriptions = [];

	public function storeSubscription( Subscription $subscription ): void {
		$subscriptionKey = array_search( $subscription, $this->subscriptions, true );
		if ( $subscriptionKey === false ) {
			$subscriptionKey = count( $this->subscriptions );
		}
		$this->subscriptions[$subscriptionKey] = $subscription;
	}

	/**
	 * @return Subscription[]
	 */
	public function getSubscriptions(): array {
		return $this->subscriptions;
	}

	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int {
		$count = 0;
		foreach ( $this->subscriptions as $sub ) {
			if ( $sub->getEmail() == $subscription->getEmail() && $subscription->getCreatedAt() > $cutoffDateTime ) {
				$count++;
			}
		}
		return $count;
	}

	public function findByConfirmationCode( string $confirmationCode ): ?Subscription {
		foreach ( $this->subscriptions as $subscription ) {
			if ( $subscription->getConfirmationCode() === $confirmationCode ) {
				return $subscription;
			}
		}

		return null;
	}

}