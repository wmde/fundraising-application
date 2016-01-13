<?php


namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\MailValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RequestValidator
{
	private $mailValidator;
	private $obligatoryFields = [
		'vorname', 'nachname', 'titel'
	];
	private $validationErrors = [];

	public function __construct( MailValidator $mailValidator ) {
		$this->mailValidator = $mailValidator;
	}

	public function validate( Request $request ) {
		// TODO use a proper validator interface on the sub-validators for each field, with a config array
		// TODO use sub-validators to generate violation messages
		// TODO store violation messages
		$errors = [];
		if ( ! $this->mailValidator->validateMail( $request->getEmail() ) ) {
			$errors['email'] = 'invalid';
		}
		foreach ( $this->obligatoryFields as $fld ) {
			$accessor = 'get' . ucfirst( $fld );
			if ( empty( $request->$accessor() ) ) {
				$errors[$fld] = 'missing';
			}

		}
		$this->validationErrors = $errors;
		return count( $errors ) == 0;
	}

	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	public function setValidationErrors( array $validationErrors ) {
		$this->validationErrors = $validationErrors;
	}
}