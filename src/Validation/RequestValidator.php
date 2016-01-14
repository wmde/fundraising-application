<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequestValidator implements InstanceValidator {

	use CanValidate;

	public function __construct( MailValidator $mailValidator ) {
		$this->setConstraints( [
			new ValidationConstraint( 'email', $mailValidator ),
			new ValidationConstraint( 'vorname', new RequiredFieldValidator() ),
			new ValidationConstraint( 'nachname', new RequiredFieldValidator() ),
			new ValidationConstraint( 'anrede', new RequiredFieldValidator() ),
		] );
	}

}