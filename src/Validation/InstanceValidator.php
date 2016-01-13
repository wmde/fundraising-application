<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface InstanceValidator
{
	/**
	 * @param mixed $instance
	 * @return bool
	 */
	public function validate( $instance ): bool;

	/**
	 * @return ConstraintViolation[]
	 */
	public function getConstraintViolations(): array;
}
