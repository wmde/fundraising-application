<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

interface StreamOpener {
	/**
	 * @return resource
	 */
	public function openStream( string $url, string $mode );
}