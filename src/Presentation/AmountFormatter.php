<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Euro\Euro;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AmountFormatter {

	private $locale;

	private $localeData = [
		'de_DE' => [ 2, ',', '' ],
		'en_US' => [ 2, '.', '' ]
	];

	public function __construct( string $locale ) {
		$this->locale = $locale;
	}

	public function format( Euro $amount ): string {
		if ( !array_key_exists( $this->locale, $this->localeData ) ) {
			throw new \RuntimeException( 'Unknown locale' );
		}
		return number_format(
			$amount->getEuroFloat(),
			$this->localeData[$this->locale][0],
			$this->localeData[$this->locale][1],
			$this->localeData[$this->locale][2]
		);
	}

}