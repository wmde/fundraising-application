<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Subscription;

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

	public function __construct( MailValidator $mailValidator, TextPolicyValidator $textPolicyValidator,
	                             SubscriptionDuplicateValidator $duplicateValidator ) {
		$this->mailValidator = $mailValidator;
		$this->textPolicyValidator = $textPolicyValidator;
		$this->duplicateValidator = $duplicateValidator;
		$this->textPolicyViolations = [];
	}

	public function validate( Subscription $subscription ): ValidationResult {
		return new ValidationResult( ...array_filter( array_merge(
			$this->getRequiredFieldViolations( $subscription ),
			[ $this->validateField( $this->mailValidator, $subscription->getEmail(), 'email' ) ],
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
		$requiredFieldValidator = new RequiredFieldValidator();
		$violations = [];
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getSalutation(), 'salutation' );
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getFirstName(), 'firstName' );
		$violations[] = $this->validateField( $requiredFieldValidator, $address->getLastName(), 'lastName' );
		$violations[] = $this->validateField( $requiredFieldValidator, $subscription->getEmail(), 'email' );
		return $violations;
	}

	private function getBadWordViolations( Subscription $subscription ) {
		$violations = [];

		$flags = TextPolicyValidator::CHECK_BADWORDS |
			TextPolicyValidator::IGNORE_WHITEWORDS |
			TextPolicyValidator::CHECK_URLS;
		$fieldTextValidator = new FieldTextPolicyValidator( $this->textPolicyValidator, $flags );
		$address = $subscription->getAddress();

		$violations[] = $this->validateField( $fieldTextValidator, $address->getFirstName(), 'firstName' );
		$violations[] = $this->validateField( $fieldTextValidator, $address->getLastName(), 'lastName' );
		$violations[] = $this->validateField( $fieldTextValidator, $address->getCompany(), 'company' );
		$violations[] = $this->validateField( $fieldTextValidator, $address->getAddress(), 'address' );
		$violations[] = $this->validateField( $fieldTextValidator, $address->getPostcode(), 'postcode' );
		$violations[] = $this->validateField( $fieldTextValidator, $address->getCity(), 'city' );
		return $violations;
	}

	/**
	 * @return ConstraintViolation[]
	 */
	public function getTextPolicyViolations(): array {
		return $this->textPolicyViolations;
	}

}