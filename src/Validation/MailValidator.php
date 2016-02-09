<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\DomainNameValidator;
use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MailValidator implements ScalarValueValidator {

	private $domainValidator;
	private $lastViolation;

	public function __construct( DomainNameValidator $tldValidator ) {
		$this->domainValidator = $tldValidator;
	}

	public function validate( $emailAddress ): bool {
		$mailAddress = null;

		try {
			$mailAddress = new MailAddress( $emailAddress );
		} catch ( \InvalidArgumentException $e ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'Address has wrong format' );
			return false;
		}

		if ( !$mailAddress || !filter_var( $mailAddress->getNormalizedAddress(), FILTER_VALIDATE_EMAIL ) ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'Address is no valid email' );
			return false;
		}

		if ( !$this->domainValidator->isValid( $mailAddress->getNormalizedDomain() ) ) {
			$this->lastViolation = new ConstraintViolation( $emailAddress, 'MX record not found' );
			return false;
		}

		return true;
	}

	public function getLastViolation(): ConstraintViolation {
		return $this->lastViolation;
	}
}
