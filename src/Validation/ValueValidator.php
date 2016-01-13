<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * Validate a single, scalar value
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface ValueValidator {
	public function validate( $value ): bool;
	public function getLastViolation(): ConstraintViolation;
}
