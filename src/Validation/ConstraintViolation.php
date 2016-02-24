<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConstraintViolation {
	private $value;
	private $messageIdentifier;
	private $source;

	/**
	 * @param mixed $value The value that caused this violation
	 * @param string $messageIdentifier identifier of the error message as defined in translation files
	 * @param string $source Class name or Class.Field name
	 */
	public function __construct( $value, string $messageIdentifier, string $source = '' ) {
		$this->value = $value;
		$this->messageIdentifier = $messageIdentifier;
		$this->source = $source;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	public function getMessageIdentifier(): string {
		return $this->messageIdentifier;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->source = $source;
	}

}
