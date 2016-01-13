<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class MailValidator implements ValueValidator {

	private $testWithMX;
	private $lastViolation;

	const TEST_WITH_MX = true;
	const TEST_WITHOUT_MX = false;

	public function __construct( bool $testWithMX ) {
		$this->testWithMX = $testWithMX;
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

		if ( $this->testWithMX ) {
			if ( !checkdnsrr( $mailAddressObject->domain, 'MX' ) && !checkdnsrr( $mailAddressObject->domain, 'A' ) ) {
				$this->lastViolation = new ConstraintViolation( $emailAddress, 'MX record not found', $this );
				return false;
			}
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
