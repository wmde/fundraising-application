<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class EmailAddress {

	private $userName;
	private $domain;

	public function __construct( string $emailAddress ) {
		$addressParts = explode( '@', $emailAddress );
		if ( is_array( $addressParts ) && count( $addressParts ) === 2 ) {
			$this->userName = $addressParts[0];
			$this->domain = $addressParts[1];
		} else {
			throw new \InvalidArgumentException( 'Given email address could not be parsed' );
		}
	}

	public function getUserName(): string {
		return $this->userName;
	}

	public function getDomain(): string {
		return $this->domain;
	}

	public function getNormalizedDomain() {
		return idn_to_ascii( $this->domain );
	}

	public function getFullAddress(): string {
		return $this->userName . '@' . $this->domain;
	}

	public function getNormalizedAddress(): string {
		return $this->userName . '@' . $this->getNormalizedDomain();
	}

}
