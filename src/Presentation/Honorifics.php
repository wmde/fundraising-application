<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * Encapsulates a list of honorifics for the current locale.
 */
class Honorifics {

	/**
	 * @param array<string,string> $honorifics name => display name pairs
	 */
	public function __construct( private readonly array $honorifics ) {
	}

	/**
	 * @return string[]
	 */
	public function getList(): array {
		return $this->honorifics;
	}

	/**
	 * @return string[]
	 */
	public function getKeys(): array {
		return array_keys( $this->honorifics );
	}

}
