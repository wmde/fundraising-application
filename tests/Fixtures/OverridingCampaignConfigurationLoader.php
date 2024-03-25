<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;

class OverridingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	/**
	 * @var callable|\Closure
	 */
	private $modifyConfiguration;

	/**
	 * @param CampaignConfigurationLoaderInterface $originalLoader
	 * @param array<string, array<string, array<int, string>|string|true>> $additionalCampaignConfiguration
	 * @param callable|null $modifyConfiguration
	 */
	public function __construct(
		private readonly CampaignConfigurationLoaderInterface $originalLoader,
		private readonly array $additionalCampaignConfiguration,
		?callable $modifyConfiguration = null
	) {
		$this->modifyConfiguration = $modifyConfiguration ?: static function ( $config ): array {
			return $config;
		};
	}

	/**
	 * @param string ...$configFiles
	 *
	 * @return CampaignConfiguration[]
	 */
	public function loadCampaignConfiguration( string ...$configFiles ): array {
		$newConfig = array_replace_recursive(
			$this->originalLoader->loadCampaignConfiguration( ...$configFiles ),
			$this->additionalCampaignConfiguration
		);
		return call_user_func( $this->modifyConfiguration, $newConfig );
	}

}
