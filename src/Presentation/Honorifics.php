<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * Encapsulates a list of honorifics for the current locale.
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class Honorifics {

	private $honorifics;

	/**
	 * @param string[] $honorifics name => display name pairs
	 */
	public function __construct( array $honorifics ) {
		$this->honorifics = $honorifics;
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