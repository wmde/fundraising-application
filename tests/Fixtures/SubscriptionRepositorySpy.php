<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SubscriptionRepositorySpy implements SubscriptionRepository {

	/**
	 * @var Subscription[]
	 */
	private $subscriptions = [];

	public function storeSubscription( Subscription $subscription ) {
		$this->subscriptions[] = $subscription;
	}

	/**
	 * @return Subscription[]
	 */
	public function getSubscriptions(): array {
		return $this->subscriptions;
	}

	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int {
		return 0;
	}

	public function findByConfirmationCode( string $confirmationCode ) {
		return null;
	}

}