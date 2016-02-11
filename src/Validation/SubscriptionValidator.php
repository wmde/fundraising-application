<?php


namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Subscription;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionValidator implements InstanceValidator {
	use CanValidateField;

	private $mailValidator;
	private $duplicateValidator;
	private $textPolicyValidator;
	private $constraintViolations;
	private $textPolicyViolations;

	public function __construct( MailValidator $mailValidator, TextPolicyValidator $textPolicyValidator,
	                             SubscriptionDuplicateValidator $duplicateValidator ) {
		$this->mailValidator = $mailValidator;
		$this->textPolicyValidator = $textPolicyValidator;
		$this->duplicateValidator = $duplicateValidator;
		$this->constraintViolations = [];
		$this->textPolicyViolations = [];
	}

	/**
	 * @param Subscription $subscription
	 *
	 * @return bool
	 */
	public function validate( $subscription ): bool {
		$violations = $this->getRequiredFieldViolations( $subscription );
		$violations[] = $this->validateField( $this->mailValidator, $subscription->getEmail(), 'email' );
		if ( ! $this->duplicateValidator->validate( $subscription ) ) {
			$violations = array_merge( $violations, $this->duplicateValidator->getConstraintViolations() );
		}
		$this->constraintViolations = array_filter( $violations );
		return count( $this->constraintViolations ) == 0;
	}

	public function needsModeration( $subscription ): bool {
		$this->textPolicyViolations = array_filter( $this->getBadWordViolations( $subscription ) );
		return count( $this->textPolicyViolations ) > 0;
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

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

	public function getTextPolicyViolations(): array {
		return $this->textPolicyViolations;
	}

}