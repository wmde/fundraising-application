<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * Validates an instance property and adds the source information to the ConstraintViolation
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidationConstraint {
	private $fieldName;
	private $validator;

	public function __construct( string $fieldName, ValueValidator $validator ) {
		$this->fieldName = $fieldName;
		$this->validator = $validator;
	}

	/**
	 * @param mixed $instance
	 * @return null|ConstraintViolation
	 */
	public function validate( $instance ) {
		$accessor = 'get' . ucfirst( $this->fieldName );
		if ( $this->validator->validate( $instance->{$accessor}() ) ) {
			return null;
		}
		$violation = $this->validator->getLastViolation();
		$violation->setSource( get_class( $instance ) . '.' . $this->fieldName );
		return $violation;
	}
}
