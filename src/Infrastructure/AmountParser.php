<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AmountParser {

	private $locale;

	public function __construct( string $locale = 'de_DE' ) {
		$this->locale = $locale;
	}

	public function parseAsFloat( string $amount ): float {
		return (float)( new \NumberFormatter( $this->locale, \NumberFormatter::DECIMAL ) )->parse( $amount );
	}

}
