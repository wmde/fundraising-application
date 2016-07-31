<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Infrastructure\DomainNameValidator;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class EmailValidator {

	private $domainValidator;

	public function __construct( DomainNameValidator $tldValidator ) {
		$this->domainValidator = $tldValidator;
	}

	public function validate( $emailAddress ): ValidationResult {
		$mailAddress = null;

		try {
			$mailAddress = new EmailAddress( $emailAddress );
		} catch ( \InvalidArgumentException $e ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'email_address_wrong_format' ) );
		}

		if ( !$mailAddress || !filter_var( $mailAddress->getNormalizedAddress(), FILTER_VALIDATE_EMAIL ) ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'email_address_invalid' ) );
		}

		if ( !$this->domainValidator->isValid( $mailAddress->getNormalizedDomain() ) ) {
			return new ValidationResult( new ConstraintViolation( $emailAddress, 'email_address_domain_record_not_found' ) );
		}

		return new ValidationResult();
	}

}
