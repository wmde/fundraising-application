<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling;

use WMDE\Fundraising\MembershipContext\EventEmitter;
use WMDE\Fundraising\MembershipContext\Domain\Event;

class MembershipEventEmitter implements EventEmitter {

	private EventDispatcher $dispatcher;

	public function __construct( EventDispatcher $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}

	public function emit( Event $event ): void {
		$this->dispatcher->dispatch( $event );
	}
}
