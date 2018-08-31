<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @license GNU GPL v2+
 */
class FileStreamOpener implements StreamOpener {
	/**
	 * @return resource
	 */
	public function openStream( string $url, string $mode ) {
		$this->createParentDirectoryIfNeeded( $url );
		$handle = @fopen( $url, $mode );
		if ( $handle === false ) {
			throw new StreamOpeningError( error_get_last()['message'] ?: 'Could not open ' . $url );
		}
		return $handle;
	}

	private function createParentDirectoryIfNeeded( string $url ): void {
		$base = dirname( $url );
		if ( !file_exists( $base ) ) {
			if ( !mkdir( $base, 0777, true ) ) {
				throw new StreamOpeningError( error_get_last()['message'] ?: 'Could not create directory ' . $base );
			}
		}
	}
}
