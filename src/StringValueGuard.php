<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StringValueGuard {

	private $whitelist = [];
	private $blacklist = [];

	public function isAllowed( string $value ): bool {
		if ( $this->whitelist !== [] && !in_array( $value, $this->whitelist ) ) {
			return false;
		}

		return !in_array( $value, $this->blacklist );
	}

	/**
	 * @param string[] $strings
	 */
	public function setWhitelist( array $strings ) {
		$this->assertAreStrings( $strings );
		$this->whitelist = $strings;
	}

	/**
	 * @param string[] $strings
	 */
	public function setBlacklist( array $strings ) {
		$this->assertAreStrings( $strings );
		$this->blacklist = $strings;
	}

	private function assertAreStrings( array $strings ) {
		foreach ( $strings as $string ) {
			if ( !is_string( $string ) ) {
				throw new \InvalidArgumentException( 'All array elements must be of type string' );
			}
		}
	}

}
