<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TemplateNameValidator {

	private $twig;

	public function __construct( \Twig_Environment $twig ) {
		$this->twig = $twig;
	}

	/**
	 * @param $value
	 * @return ValidationResult+
	 * @throws \Twig_Error_Syntax
	 */
	public function validate( $value ): ValidationResult {
		try {
			$this->twig->loadTemplate( $value );
		}
		catch ( \Twig_Error_Loader $e ) {
			return new ValidationResult( new ConstraintViolation( $value, 'Could not find template' ) );
		}

		return new ValidationResult();
	}
}
