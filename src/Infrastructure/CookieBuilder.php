<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CookieBuilder {

	private $expire;
	private $path;
	private $domain;
	private $secure;
	private $httpOnly;
	private $raw;
	private $sameSite;

	public function __construct( int $expire = 0, string $path = '', string $domain = null, bool $secure = false,
								 bool $httpOnly = false, bool $raw = false, string $sameSite = null ) {
		$this->expire = $expire;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->httpOnly = $httpOnly;
		$this->raw = $raw;
		$this->sameSite = $sameSite;
	}

	public function newCookie( string $name, string $value ): Cookie {
		return new Cookie(
			$name,
			$value,
			$this->expire,
			$this->path,
			$this->domain,
			$this->secure,
			$this->httpOnly,
			$this->raw,
			$this->sameSite
		);
	}

}
