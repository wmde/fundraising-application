<?php


namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Validation\MailValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequestValidator implements InstanceValidator {

	private $constraints;
	private $constraintViolations;

	public function __construct( MailValidator $mailValidator ) {
		$this->constraints = [
			new ValidationConstraint( 'email', $mailValidator ),
			new ValidationConstraint( 'vorname', new RequiredFieldValidator() ),
			new ValidationConstraint( 'nachname', new RequiredFieldValidator() ),
			new ValidationConstraint( 'anrede', new RequiredFieldValidator() ),
		];
	}

	public function validate( $request ): bool {
		$this->constraintViolations = array_filter( array_map(
				function( ValidationConstraint $constraint ) use ( $request ) {
					return $constraint->validate( $request );
				},
			$this->constraints
		) );
		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

}