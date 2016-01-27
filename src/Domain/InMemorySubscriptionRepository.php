<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Subscription;

/**
 * TODO Not sure if this is actually needed for unit tests if the DoctrineRequestRepository is used
 * with an in-memory sqlite database. Until we have a proper DI container, this class can't be initialized in
 * FunFunFactory
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class InMemorySubscriptionRepository implements SubscriptionRepository {

	private $subscriptions;

	/**
	 * @param Subscription[] $requests
	 */
	public function __construct( array $requests ) {
		$this->subscriptions = $requests;
	}

	public function storeSubscription( Subscription $subscription ) {
		$this->subscriptions[] = $subscription;
	}

	public function getSubscriptions(): array {
		return $this->subscriptions;
	}

	public function setSubscriptions( array $subscriptions ) {
		$this->subscriptions = $subscriptions;
	}
}