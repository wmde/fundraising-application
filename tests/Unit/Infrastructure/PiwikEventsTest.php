<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikEventsTest extends \PHPUnit\Framework\TestCase {

	public function testWhenPassedAnUndefinedCustomVarId_exceptionIsThrown() {
		$piwikEvents = new PiwikEvents();
		$this->expectException( \InvalidArgumentException::class );
		$piwikEvents->triggerSetCustomVariable( 4095, 'an event has no definition', PiwikEvents::SCOPE_PAGE );
	}

	public function testWhenPassedValidCustomVarId_eventIsAdded() {
		$piwikEvents = new PiwikEvents();
		$piwikEvents->triggerSetCustomVariable(
			PiwikEvents::CUSTOM_VARIABLE_PAYMENT_TYPE,
			'some value',
			PiwikEvents::SCOPE_PAGE
		);
		$piwikEvents->triggerSetCustomVariable(
			PiwikEvents::CUSTOM_VARIABLE_PAYMENT_INTERVAL,
			'some other value',
			PiwikEvents::SCOPE_VISIT
		);

		$this->assertContains(
			[ 'setCustomVariable', 1, 'Payment', 'some value', 'page' ],
			$piwikEvents->getEvents()
		);
		$this->assertContains(
			[ 'setCustomVariable', 3, 'Interval', 'some other value', 'visit' ],
			$piwikEvents->getEvents()
		);
	}

	public function testWhenCallingTrackGoal_eventIsAdded() {
		$piwikEvents = new PiwikEvents();
		$piwikEvents->triggerTrackGoal( 4 );
		$this->assertContains(
			[ 'trackGoal', 4 ],
			$piwikEvents->getEvents()
		);
	}

}
