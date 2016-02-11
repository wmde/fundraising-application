<?php


namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Subscription;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface SubscriptionRepository {

	public function storeSubscription( Subscription $subscription );

	/**
	 * Count the number of subscriptions with the same email address that were created after the cutoff date.
	 *
	 * @param Subscription $subscription
	 * @param \DateTime $cutoffDateTime
	 * @return int
	 */
	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int;

	/**
	 * @param string $confirmationCode
	 * @return Subscription|null
	 */
	public function findByConfirmationCode( string $confirmationCode );

}