<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConstraintViolation {
	private $value;
	private $message;
	private $source;
	private $constraint;

	/**
	 * @param mixed $value The value that caused this violation
	 * @param string $message Error message
	 * @param mixed $constraint The validator instance that created this
	 * @param string $source Class name or Class.Field name
	 */
	public function __construct( $value, string $message, $constraint = null, string $source = '' ) {
		$this->value = $value;
		$this->message = $message;
		$this->source = $source;
		$this->constraint = $constraint;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->source = $source;
	}

	/**
	 * @return mixed|null
	 */
	public function getConstraint() {
		return $this->constraint;
	}
}
