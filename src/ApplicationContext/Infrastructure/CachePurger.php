<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\CachePurgingException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface CachePurger {

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache();

}
