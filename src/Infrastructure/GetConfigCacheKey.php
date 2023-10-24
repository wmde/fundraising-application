<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * This trait creates cache keys for configuration files (probably in app/cache),
 * incorporating their modification date, which gives us an "auto-purge" of the cache
 * when the file changes.
 */
trait GetConfigCacheKey {
	/**
	 * @param string ...$configFiles Usually called with one file, use multiple files in cases where they override each other
	 * @return string
	 */
	protected function getCacheKey( string ...$configFiles ): string {
		$fileStats = '';
		foreach ( $configFiles as $file ) {
			if ( file_exists( $file ) ) {
				$fileStats .= sprintf( ",%s.%d", $file, filemtime( $file ) );
			}
		}
		return strlen( $fileStats ) > 0 ? md5( $fileStats ) : '';
	}
}
