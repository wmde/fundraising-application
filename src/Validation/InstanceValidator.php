<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface InstanceValidator {

	public function validate( $instance ): bool;

	/**
	 * @return ConstraintViolation[]
	 */
	public function getConstraintViolations(): array;

}
