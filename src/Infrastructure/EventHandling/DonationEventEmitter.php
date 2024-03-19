<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling;

use WMDE\Fundraising\DonationContext\Domain\Event;
use WMDE\Fundraising\DonationContext\EventEmitter;

class DonationEventEmitter implements EventEmitter {

	public function __construct( private readonly EventDispatcher $dispatcher ) {
	}

	public function emit( Event $event ): void {
		$this->dispatcher->dispatch( $event );
	}
}
