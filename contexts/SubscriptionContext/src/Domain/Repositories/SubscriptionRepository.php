<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories;

use WMDE\Fundraising\Entities\Subscription;

/**
 * FIXME: this repository exposes data access layer details (should use a domain object instead)
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface SubscriptionRepository {

	/**
	 * Add or update a subscription.
	 *
	 * If the subscription exists in the repository, update it.
	 *
	 * @param Subscription $subscription
	 * @throws SubscriptionRepositoryException
	 */
	public function storeSubscription( Subscription $subscription );

	/**
	 * Count the number of subscriptions with the same email address that were created after the cutoff date.
	 *
	 * @param Subscription $subscription
	 * @param \DateTime $cutoffDateTime
	 * @return int
	 * @throws SubscriptionRepositoryException
	 */
	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int;

	/**
	 * @param string $confirmationCode
	 * @return Subscription|null
	 * @throws SubscriptionRepositoryException
	 */
	public function findByConfirmationCode( string $confirmationCode );

}
