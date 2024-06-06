<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Maps a list of ConstraintViolations into an array of messages grouped by the property causing them
 */
class ConstraintViolationListMapper {

	private PropertyAccessor $propertyAccessor;

	public function __construct() {
		$this->propertyAccessor = PropertyAccess::createPropertyAccessor();
	}

	/**
	 * @return mixed[]
	 */
	public function map( ConstraintViolationListInterface $violations ): array {
		$errors = [];

		foreach ( $violations as $violation ) {
			/** @var ConstraintViolationInterface $violation */
			$entryErrors = (array)$this->propertyAccessor->getValue( $errors, $violation->getPropertyPath() );
			$entryErrors[] = $violation->getMessage();
			$this->propertyAccessor->setValue( $errors, $violation->getPropertyPath(), $entryErrors );
		}

		return $errors;
	}
}
