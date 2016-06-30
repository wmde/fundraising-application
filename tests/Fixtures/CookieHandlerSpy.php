<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\CookieHandler;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CookieHandlerSpy implements CookieHandler {

	private $cookieJar = [];
	private $setCount = 0;

	public function getCookie( $key ) {
		return array_key_exists( $key, $this->cookieJar ) ? $this->cookieJar[$key] : '';
	}

	public function setCookie( $key, $value ) {
		$this->cookieJar[$key] = $value;
		$this->setCount ++;
	}

	public function getCookies() {
		return $this->cookieJar;
	}

	public function getSetCalls() {
		return $this->setCount;
	}

}
