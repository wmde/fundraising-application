<?php


namespace WMDE\Fundraising\Frontend\Validation;

trait CanValidate {

	private $constraints = [];
	private $constraintViolations = [];

	/**
	 * @param mixed $instance
	 * @return bool
	 */
	public function validate( $instance ): bool {
		$this->constraintViolations = array_filter( array_map(
			function( ValidationConstraint $constraint ) use ( $instance ) {
				return $constraint->validate( $instance );
			},
			$this->constraints
		) );
		return count( $this->constraintViolations ) == 0;
	}

	/**
	 * @return ConstraintViolation[]
	 */
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