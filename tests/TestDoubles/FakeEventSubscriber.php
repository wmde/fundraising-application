<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

class FakeEventSubscriber implements EventSubscriber {
	public function getSubscribedEvents() {
		return [
			'onFlush'
		];
	}

	public function onFlush( EventArgs $args ): void {
		// Do nothing
	}

}
