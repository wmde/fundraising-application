<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use InvalidArgumentException;

class SkinManager {

	public const QUERY_PARAM_NAME = 'skin';
	public const COOKIE_NAME = 'skin';

	/**
	 * @var string[]
	 */
	private $skins = [];
	private $defaultSkin;
	private $cookieLifetime = 0;

	private $skin;

	public function __construct( array $skins, string $defaultSkin, int $cookieLifetime ) {
		$this->skins = $skins;
		$this->defaultSkin = $defaultSkin;
		$this->cookieLifetime = $cookieLifetime;

		$this->setSkin( $this->defaultSkin );
	}

	public function setSkin( string $skin ): void {
		if ( !in_array( $skin, $this->skins, true ) ) {
			throw new InvalidArgumentException( "'$skin' is not a valid skin name" );
		}

		$this->skin = $skin;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function isValidSkin( string $skin ): bool {
		return in_array( $skin, $this->skins, true );
	}

	public function getDefaultSkin(): string {
		return $this->defaultSkin;
	}

	public function getCookieLifetime(): int {
		return $this->cookieLifetime;
	}
}

