<?php

namespace WMDE\Fundraising\Frontend\Domain;

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
	 * Get list of honorifics.
	 *
	 * @return array
	 */
	public function getList() {
		return $this->honorifics;
	}

	public function getKeys() {
		return array_keys( $this->honorifics );
	}

}