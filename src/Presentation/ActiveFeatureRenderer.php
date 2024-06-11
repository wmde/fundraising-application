<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\FeatureToggle\Feature;

class ActiveFeatureRenderer {

	/**
	 * @return string[]
	 */
	public static function renderActiveFeatureIds( Feature ...$features ): array {
		$ids = [];
		foreach ( $features as $feature ) {
			if ( $feature->active ) {
				$ids[] = $feature->getId();
			}
		}
		return $ids;
	}
}
