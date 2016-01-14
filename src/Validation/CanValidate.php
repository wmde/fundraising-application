<?php


namespace WMDE\Fundraising\Frontend\Validation;


trait CanValidate {

	private $constraints;
	private $constraintViolations = [];

	public function validate( $instance ): bool {
		$this->constraintViolations = array_filter( array_map(
			function( ValidationConstraint $constraint ) use ( $instance ) {
				return $constraint->validate( $instance );
			},
			$this->constraints
		) );
		return count( $this->constraintViolations ) == 0;
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}

	/**
	 * @param ValidationConstraint[] $constraints
	 */
	public function setConstraints( array $constraints ) {
		$this->constraints = $constraints;
	}

}