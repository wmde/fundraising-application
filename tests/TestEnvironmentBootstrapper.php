<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

class TestEnvironmentBootstrapper extends EnvironmentBootstrapper {
	private array $configurationOverride = [];

	public function overrideConfiguration( array $config ): void {
		$this->configurationOverride = $config;
	}

	protected function getConfiguration(): array {
		$config = parent::getConfiguration();
		if ( $this->configurationOverride ) {
			$config = \array_replace_recursive( $config, $this->configurationOverride );
		}
		return $config;
	}

}
