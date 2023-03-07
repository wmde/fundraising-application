<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

class FakeEventSubscriber implements EventSubscriber {
	/**
	 * @return string[]
	 */
	public function getSubscribedEvents(): array {
		return [
			'onFlush'
		];
	}

	public function onFlush( EventArgs $args ): void {
		// Do nothing
	}

}
