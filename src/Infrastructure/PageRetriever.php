<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen
 * @author Christoph Fischer
 */
interface PageRetriever {

	const MODE_RAW = 'raw';
	const MODE_RENDERED = 'render';

	/**
	 * Should return an empty string on error.
	 *
	 * @param string $pageName
	 * @param string $fetchMode TODO: it seems like the only mode ever used is PageRetriever::MODE_RAW
	 *
	 * @return string
	 */
	public function fetchPage( string $pageName, string $fetchMode ): string;

}
