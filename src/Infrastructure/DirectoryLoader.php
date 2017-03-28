<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DirectoryLoader extends ArrayLoader {

	public function load( $path, $locale, $domain = 'messages' ) {
		if ( !stream_is_local( $path ) ) {
			throw new InvalidResourceException( 'Given resource ' . $path . ' is not local.' );
		}

		if ( !file_exists( $path ) || !is_dir( $path ) ) {
			throw new NotFoundResourceException( 'Directory ' . $path . ' not found.' );
		}

		$messages = $this->loadResource( $path );
		$catalogue = parent::load( $messages, $locale, $domain );

		return $catalogue;
	}

	private function loadResource( string $basePath ): array {
		$messages = [];
		$path = $basePath . '/pages';

		$files = array_diff( scandir( $path ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			if ( is_file( $path . '/' . $file ) ) {
				$messages[$file] = file_get_contents( $path . '/' . $file );
			}
		}

		return $messages;
	}

}
