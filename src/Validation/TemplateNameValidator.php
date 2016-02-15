<?php

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TemplateNameValidator {

	private $twig;
	private $lastViolation;


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
			return new ValidationResult();
		}
		catch (\Twig_Error_Loader $e ) {
			return new ValidationResult( new ConstraintViolation( $value, 'Could not find template' ) );
		}

	}
}