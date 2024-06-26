<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\EventHandling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\MembershipEventEmitter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeMembershipEvent;

#[CoversClass( MembershipEventEmitter::class )]
class MembershipEventEmitterTest extends TestCase {

	public function testEmitterDispatchesEvent(): void {
		$event = new FakeMembershipEvent();
		$dispatcher = $this->createMock( EventDispatcher::class );
		$dispatcher->expects( $this->once() )->method( 'dispatch' )->with( $event );
		$emitter = new MembershipEventEmitter( $dispatcher );

		$emitter->emit( $event );
	}
}
