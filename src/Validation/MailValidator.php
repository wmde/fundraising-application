<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\DomainNameValidator;
use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MailValidator {

	private $domainValidator;

	public function __construct( DomainNameValidator $tldValidator ) {
		$this->domainValidator = $tldValidator;
	}

	public function validate( $emailAddress ): ValidationResult {
		$mailAddress = null;

		try {
			$mailAddress = new MailAddress( $emailAddress );
		} catch ( \InvalidArgumentException $e ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'Address has wrong format' ) );
		}

		if ( !$mailAddress || !filter_var( $mailAddress->getNormalizedAddress(), FILTER_VALIDATE_EMAIL ) ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'Address is no valid email' ) );
		}

		if ( !$this->domainValidator->isValid( $mailAddress->getNormalizedDomain() ) ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'MX record not found' ) );
		}

		return new ValidationResult();
	}

}
