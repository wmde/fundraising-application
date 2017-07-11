<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Simple wrapper for tracking methods in Piwik's API
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikEvents {

	const SCOPE_VISIT = 'visit';
	const SCOPE_PAGE = 'page';

	const EVENT_SET_CUSTOM_VARIABLE = 'setCustomVariable';
	const EVENT_TRACK_GOAL = 'trackGoal';

	const CUSTOM_VARIABLE_PAYMENT_TYPE = 1;
	const CUSTOM_VARIABLE_AMOUNT = 2;
	const CUSTOM_VARIABLE_PAYMENT_INTERVAL = 3;

	private $variableNames = [
		1 => 'Payment',
		2 => 'Amount',
		3 => 'Interval'
	];

	private $events = [];

	/**
	 * @param int $variableId
	 * @param string $value
	 * @param string $scope
	 *
	 * @throws \InvalidArgumentException
	 */
	public function triggerSetCustomVariable( int $variableId, string $value, string $scope ): void {
		$this->events[] = [ self::EVENT_SET_CUSTOM_VARIABLE, $variableId, $this->getVariableName( $variableId ), $value, $scope ];
	}

	public function triggerTrackGoal( int $goalId ): void {
		$this->events[] = [ self::EVENT_TRACK_GOAL, $goalId ];
	}

	public function getEvents(): array {
		return $this->events;
	}

	/**
	 * @param int $variableId
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function getVariableName( int $variableId ): string {
		if ( !array_key_exists( $variableId, $this->variableNames ) ) {
			throw new \InvalidArgumentException( 'The given variable ID is not defined' );
		}
		return $this->variableNames[$variableId];
	}
}
