<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface CachePurger {

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache(): void;

}
