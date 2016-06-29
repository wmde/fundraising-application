<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class BrowserCookieHandler implements CookieHandler {

	private $cookieJar;

	public function __construct( ParameterBag $cookieJar ) {
		$this->cookieJar = $cookieJar;
	}

	public function getCookie( $key ) {
		return $this->cookieJar->get( $key, '' );
	}

	public function setCookie( $key, $value ) {
		$this->cookieJar->set( $key, $value );
	}

	public function getCookies() {
		return $this->cookieJar->all();
	}
}
