<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling;

use WMDE\Fundraising\MembershipContext\Domain\Event;
use WMDE\Fundraising\MembershipContext\EventEmitter;

class MembershipEventEmitter implements EventEmitter {

	public function __construct( private readonly EventDispatcher $dispatcher ) {
	}

	public function emit( Event $event ): void {
		$this->dispatcher->dispatch( $event );
	}
}
