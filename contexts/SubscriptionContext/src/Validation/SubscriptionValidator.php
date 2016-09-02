<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Validation;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepositoryException;
use WMDE\Fundraising\Frontend\Validation\AllowedValuesValidator;
use WMDE\Fundraising\Frontend\Validation\CanValidateField;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\RequiredFieldValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionValidator {
	use CanValidateField;

	private $mailValidator;
	private $duplicateValidator;
	private $textPolicyValidator;
	private $textPolicyViolations;
	private $titleValidator;

	public function __construct( EmailValidator $mailValidator, TextPolicyValidator $textPolicyValidator,
								 SubscriptionDuplicateValidator $duplicateValidator,
								 AllowedValuesValidator $titleValidator ) {
		$this->mailValidator = $mailValidator;
		$this->textPolicyValidator = $textPolicyValidator;
		$this->duplicateValidator = $duplicateValidator;
		$this->titleValidator = $titleValidator;
		$this->textPolicyViolations = [];
	}

	/**
	 * @param Subscription $subscription
	 * @return ValidationResult
	 * @throws SubscriptionRepositoryException
	 */
	public function validate( Subscription $subscription ): ValidationResult {
		return new ValidationResult( ...array_filter( array_merge(
			$this->getRequiredFieldViolations( $subscription ),
			[ $this->getFieldViolation( $this->mailValidator->validate( $subscription->getEmail() ), 'email' ) ],
			[ $this->getFieldViolation(
				$this->titleValidator->validate( $subscription->getAddress()->getTitle() ),
				'title'
			) ],
			$this->duplicateValidator->validate( $subscription )->getViolations() )
		) );
	}

	public function needsModeration( $subscription ): bool {
		$this->textPolicyViolations = array_filter(
			$this->getBadWordViolations( $subscription )
		);

		return !empty( $this->textPolicyViolations );
	}

	private function getRequiredFieldViolations( Subscription $subscription ): array {
		$address = $subscription->getAddress();
		$validator = new RequiredFieldValidator();

		return [
			$this->getFieldViolation( $validator->validate( $address->getSalutation() ), 'salutation' ),
			$this->getFieldViolation( $validator->validate( $address->getFirstName() ), 'firstName' ),
			$this->getFieldViolation( $validator->validate( $address->getLastName() ), 'lastName' ),
			$this->getFieldViolation( $validator->validate( $subscription->getEmail() ), 'email' )
		];
	}

	private function getBadWordViolations( Subscription $subscription ) {
		$fieldTextValidator = new FieldTextPolicyValidator( $this->textPolicyValidator );
		$address = $subscription->getAddress();

		return [
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getFirstName() ), 'firstName' ),
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getLastName() ), 'lastName' ),
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getCompany() ), 'company' ),
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getAddress() ), 'address' ),
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getPostcode() ), 'postcode' ),
			$this->getFieldViolation( $fieldTextValidator->validate( $address->getCity() ), 'city' )
		];
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getTextPolicyViolations(): array {
		return $this->textPolicyViolations;
	}

}