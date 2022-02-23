<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\DonationContext\Domain\Event as DonationEvent;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\MembershipContext\Domain\Event as MembershipEvent;

class EventDispatcherSpy extends EventDispatcher {

	/**
	 * @param DonationEvent|MembershipEvent $event
	 */
	public function dispatch( $event ): void {
		// do nothing, currently this class is just for checking that the right event listeners are initilized
	}

	public function getObservedEventClassNames(): array {
		return array_keys( $this->listeners );
	}
}
