<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription;

use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionUseCase {

	private $subscriptionRepository;

	public function __construct( SubscriptionRepository $subscriptionRepository ) {
		$this->subscriptionRepository = $subscriptionRepository;
	}

	public function confirmSubscription( string $confirmationCode ) {
		// TODO Look for subscription confirmation code
		// TODO if found && state == unconfirmed -> change state and show success page
		// TODO else show failure page
	}
}