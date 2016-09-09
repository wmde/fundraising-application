<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ReferrerGeneralizer {

	private $defaultValue;
	private $domainMap;

	/**
	 * @param string $defaultValue
	 * @param string[] $domainMap
	 */
	public function __construct( string $defaultValue, array $domainMap ) {
		$this->defaultValue = $defaultValue;
		$this->domainMap = $domainMap;
	}

	public function generalize( string $referrer ) {
		$parsedUrl = parse_url( $referrer );
		if ( array_key_exists( 'host', $parsedUrl ) && array_key_exists( $parsedUrl['host'], $this->domainMap ) ) {
			return $this->domainMap[$parsedUrl['host']];
		}

		return $this->defaultValue;
	}

}
