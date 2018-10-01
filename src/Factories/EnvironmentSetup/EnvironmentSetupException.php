<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

class EnvironmentSetupException extends \RuntimeException {

	public function __construct( string $environmentName ) {
		parent::__construct( sprintf( 'Environment "%s" not found.', $environmentName ) );
	}
}