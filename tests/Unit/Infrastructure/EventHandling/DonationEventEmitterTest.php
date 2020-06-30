<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\EventHandling;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DonationEventEmitter;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationEvent;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DonationEventEmitter
 */
class DonationEventEmitterTest extends TestCase {

	public function testEmitterDispatchesEvent(): void {
		$event = new FakeDonationEvent();
		$dispatcher = $this->createMock( EventDispatcher::class );
		$dispatcher->expects( $this->once() )->method( 'dispatch' )->with( $event );
		$emitter = new DonationEventEmitter( $dispatcher );

		$emitter->emit( $event );
	}
}
