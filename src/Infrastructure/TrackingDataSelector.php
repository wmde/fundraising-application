<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

class TrackingDataSelector {

	/**
	 * @param string[] $values
	 */
	public static function getFirstNonEmptyValue( array $values ): string {
		$nonEmptyValues = array_filter( $values );
		return count( $nonEmptyValues ) > 0 ? array_shift( $nonEmptyValues ) : '';
	}

	public static function concatTrackingFromVarTuple( string $campaign, string $keyword ): string {
		if ( $campaign !== '' ) {
			return strtolower( implode( '/', array_filter( [ $campaign, $keyword ] ) ) );
		}

		return '';
	}
}
