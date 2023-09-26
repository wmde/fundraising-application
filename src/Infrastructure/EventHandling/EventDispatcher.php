<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling;

use WMDE\Fundraising\DonationContext\Domain\Event as DonationEvent;
use WMDE\Fundraising\MembershipContext\Domain\Event as MembershipEvent;

class EventDispatcher {

	/**
	 * @var callable[][]
	 */
	protected array $listeners = [];

	public function addEventListener( string $eventClassName, callable $listener ): self {
		if ( empty( $this->listeners[$eventClassName] ) ) {
			$this->listeners[$eventClassName] = [];
		}
		$this->listeners[$eventClassName][] = $listener;
		return $this;
	}

	public function dispatch( DonationEvent|MembershipEvent $event ): void {
		$eventName = get_class( $event );
		if ( empty( $this->listeners[$eventName] ) ) {
			return;
		}
		array_map( fn ( $handler ) => \call_user_func( $handler, $event ), $this->listeners[$eventName] );
	}
}
