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
	 * @param string $confirmationCode
	 * @return Subscription|null
	 */
	public function findByConfirmationCode( string $confirmationCode );
}