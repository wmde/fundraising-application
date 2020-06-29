<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Simple wrapper for tracking methods in Piwik's API
 *
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikEvents {

	public const SCOPE_VISIT = 'visit';
	public const SCOPE_PAGE = 'page';

	private const EVENT_SET_CUSTOM_VARIABLE = 'setCustomVariable';
	private const EVENT_TRACK_GOAL = 'trackGoal';

	public const CUSTOM_VARIABLE_PAYMENT_TYPE = 1;
	public const CUSTOM_VARIABLE_AMOUNT = 2;
	public const CUSTOM_VARIABLE_PAYMENT_INTERVAL = 3;

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
