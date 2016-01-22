<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\DomainNameValidator;
use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class MailValidator implements ScalarValueValidator {

	private $domainValidator;
	private $lastViolation;

	public function __construct( DomainNameValidator $tldValidator ) {
		$this->domainValidator = $tldValidator;
	}

	public function validate( $emailAddress ): bool {
		$mailAddressObject = $this->getAddressObjectFromString( $emailAddress );
		if ( !$mailAddressObject ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'Address has wrong format', $this );
			return false;
		}

		$mailAddressObject = $this->normalizeMailAddress( $mailAddressObject );
		if ( !$mailAddressObject || !filter_var( $mailAddressObject->getString(), FILTER_VALIDATE_EMAIL ) ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'Address is no valid email', $this );
			return false;
		}

		if ( !$this->domainValidator->isValid( $mailAddressObject->domain ) ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'MX record not found', $this );
			return false;
		}

		return true;
	}

	private function getAddressObjectFromString( string $emailAddress ) {
		$addressParts = explode( '@', $emailAddress );
		if ( is_array( $addressParts ) && isset( $addressParts[1] ) ) {
			return new MailAddress( $addressParts[0], $addressParts[1] );
		}

		return false;
	}

	private function normalizeMailAddress( MailAddress $mailAddressObject ) {
		$convertedDomain = idn_to_ascii( $mailAddressObject->domain );

		if ( $convertedDomain ) {
			$mailAddressObject->domain = $convertedDomain;
			return $mailAddressObject;
		}

		return false;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}
}
