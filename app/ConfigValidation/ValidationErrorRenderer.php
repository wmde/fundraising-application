<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\ConfigValidation;

use League\JsonGuard\ValidationError;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidationErrorRenderer {

	private $schema;

	public function __construct( \stdClass $schema ) {
		$this->schema = $schema;
	}

	public function render( ValidationError $error ) {
		switch ( $error->getCode() ) {
			case \League\JsonGuard\ErrorCode::MISSING_REQUIRED:
				$keys = array_keys( get_object_vars( $error->getValue() ) );
				$missingKeys = array_diff( $error->getConstraints()['required'], $keys );
				return sprintf( "Error in '%s': Missing the following keys: %s", $error->getPointer(), implode( ',', $missingKeys ) );
			case \League\JsonGuard\ErrorCode::NOT_ALLOWED_PROPERTY:
				// TODO Use pointer to navigate to schema properties
				if ( $error->getPointer() === '' ) {
					$keys = array_keys( get_object_vars( $error->getValue() ) );
					$allowedProperties = array_keys( get_object_vars( $this->schema->properties ) );
					$additionalKeys = array_diff( $keys, $allowedProperties );
					return sprintf( 'Error in root config object: Unspecified keys - %s', implode( ',', $additionalKeys ) );
				} else {
					return sprintf( "Unspecified property in '%s', please check the object keys.", $error->getPointer() );
				}
			default:
				return sprintf( "Error in '%s': %s\n", $error->getPointer(), $error->getMessage() );
		}
	}
}