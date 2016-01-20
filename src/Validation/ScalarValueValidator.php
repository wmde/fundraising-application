<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface ScalarValueValidator {
	public function validate( $value ): bool;
	public function getLastViolation(): ConstraintViolation;
}
