<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FilePrefixer {

	private $filePrefix;

	public function __construct( string $filePrefix ) {
		$this->filePrefix = $filePrefix;
	}

	public function prefixFile( string $filename ): string {
		if ( !$this->filePrefix ) {
			return $filename;
		}
		$pathParts = pathinfo( $filename );
		$dirPrefix = $pathParts['dirname'] === '.' ? '' : $pathParts['dirname'] . DIRECTORY_SEPARATOR;
		return $dirPrefix . $this->filePrefix . '.' . $pathParts['basename'];
	}

}