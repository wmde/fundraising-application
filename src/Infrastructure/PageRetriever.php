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

	/**
	 * @param string $pageName
	 * @param string $fetchMode
	 * @return string
	 */
	public function fetchPage( string $pageName, string $fetchMode ): string;

}
