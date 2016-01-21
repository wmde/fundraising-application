<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MailAddress {
	private $userName;
	private $domain;
	private $displayName;

	public function __construct( string $emailAddress, string $displayName = '' ) {
		$addressParts = explode( '@', $emailAddress );
		if ( is_array( $addressParts ) && count( $addressParts ) === 2 ) {
			$this->userName = $addressParts[0];
			$this->domain = $addressParts[1];
			$this->displayName = $displayName;
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

	public function getDisplayName(): string {
		return $this->displayName;
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
