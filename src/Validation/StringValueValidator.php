<?php


namespace WMDE\Fundraising\Frontend\Validation;

/**
 * Validate a single, scalar value
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface StringValueValidator
{
	public function validate( string $value ): bool;
}