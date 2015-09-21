<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class MailValidator {

	private $testWithMX;

	const TEST_WITH_MX = true;
	const TEST_WITHOUT_MX = false;

	/**
	 * @param bool $testWithMX
	 */
	public function __construct( $testWithMX ) {
		$this->testWithMX = $testWithMX;
	}

	/**
	 * @param string $emailAddress
	 * @return bool
	 */
	public function validateMail( $emailAddress ) {
		$mailAddressObject = $this->getAddressObjectFromString( $emailAddress );
		if ( !$mailAddressObject ) {
			return false;
		}

		$mailAddressObject = $this->normalizeMailAddress( $mailAddressObject );
		if ( !$mailAddressObject || !filter_var( $mailAddressObject->getString(), FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}

		if ( $this->testWithMX ) {
			if ( !checkdnsrr( $mailAddressObject->domain, 'MX' ) && !checkdnsrr( $mailAddressObject->domain, 'A' ) ) {
				return false;
			}
		}

		return true;
	}

	private function getAddressObjectFromString( $emailAddress ) {
		$addressParts = explode( '@', $emailAddress );
		if ( is_array( $addressParts ) && isset( $addressParts[1] ) ) {
			return new MailAddress( $addressParts[0], $addressParts[1] );
		}

		return false;
	}

	private function normalizeMailAddress( $mailAddressObject ) {
		$convertedDomain = idn_to_ascii( $mailAddressObject->domain );

		if ( $convertedDomain ) {
			$mailAddressObject->domain = $convertedDomain;
			return $mailAddressObject;
		}

		return false;
	}
}
