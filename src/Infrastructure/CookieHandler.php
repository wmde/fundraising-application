<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface CookieHandler {

	public function getCookie( $key );

	public function setCookie( $key, $value );

	public function getCookies();

}
