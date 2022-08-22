<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;

/**
 * @license GPL-2.0-or-later
 */
class OverridingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private $additionalCampaignConfiguration;
	private $originalLoader;
	private $modifyConfiguration;

	public function __construct(
		CampaignConfigurationLoaderInterface $originalLoader,
		array $additionalCampaignConfiguration,
		?callable $modifyConfiguration = null
	) {
		$this->additionalCampaignConfiguration = $additionalCampaignConfiguration;
		$this->originalLoader = $originalLoader;
		$this->modifyConfiguration = $modifyConfiguration ?: static function ( $config ): array {
			return $config;
		};
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$newConfig = array_replace_recursive(
			$this->originalLoader->loadCampaignConfiguration( ...$configFiles ),
			$this->additionalCampaignConfiguration
		);
		return call_user_func( $this->modifyConfiguration, $newConfig );
	}

}
