<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling;

use WMDE\Fundraising\DonationContext\Domain\Event as DonationEvent;
use WMDE\Fundraising\MembershipContext\Domain\Event as MembershipEvent;

class EventDispatcher {

	/**
	 * @var callable[][]
	 */
	private array $listeners = [];

	public function addEventListener( string $eventClassName, callable $listener ): self {
		assert( class_exists( $eventClassName ) );
		if ( empty( $this->listeners[$eventClassName] ) ) {
			$this->listeners[$eventClassName] = [];
		}
		$this->listeners[$eventClassName][] = $listener;
		return $this;
	}

	/**
	 * @param DonationEvent|MembershipEvent $event
	 */
	public function dispatch( $event ): void {
		$eventName = get_class( $event );
		if ( empty( $this->listeners[$eventName] ) ) {
			return;
		}
		array_map( fn( $handler ) => \call_user_func( $handler, $event ), $this->listeners[$eventName] );
	}
}
