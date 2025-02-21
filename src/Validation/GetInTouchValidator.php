<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;
use WMDE\FunValidators\Validators\EmailValidator;
use WMDE\FunValidators\Validators\IntegerValueValidator;
use WMDE\FunValidators\Validators\RequiredFieldValidator;

class GetInTouchValidator {

	public function __construct( private readonly EmailValidator $mailValidator ) {
	}

	public function validate( GetInTouchRequest $request ): ValidationResult {
		$requiredFieldValidator = new RequiredFieldValidator();
		$integerValueValidator = new IntegerValueValidator();

		$allValidationResults = [
			$requiredFieldValidator->validate( $request->getSubject() )->setSourceForAllViolations( 'subject' ),
			$requiredFieldValidator->validate( $request->getCategory() )->setSourceForAllViolations( 'category' ),
			$requiredFieldValidator->validate( $request->getMessageBody() )->setSourceForAllViolations( 'messageBody' ),
			// Email is required and has to be valid, so we check it with two validators
			$requiredFieldValidator->validate( $request->getEmailAddress() )->setSourceForAllViolations( 'email' ),
			$this->mailValidator->validate( $request->getEmailAddress() )->setSourceForAllViolations( 'email' ),
		];
		// Donation number is optional, but has to be integer if set
		if ( $request->getDonationNumber() ) {
			$allValidationResults[] = $integerValueValidator->validate( $request->getDonationNumber() )->setSourceForAllViolations( 'donationNumber' );
		}

		return new ValidationResult( ...$this->getConstraintViolationsFromResults( $allValidationResults ) );
	}

	/**
	 * @param ValidationResult[] $allValidationResults
	 * @return ConstraintViolation[]
	 */
	private function getConstraintViolationsFromResults( array $allValidationResults ): array {
		$constraintViolations = [];
		foreach ( $allValidationResults as $validationResult ) {
			$constraintViolations[] = $validationResult->getFirstViolation();
		}
		// Remove null values from validation results without errors (getFirstViolation returns null in that case)
		return array_filter( $constraintViolations );
	}

}
