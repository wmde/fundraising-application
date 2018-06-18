<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\CampaignConfigurationLoaderInterface;

/**
 * @license GNU GPL v2+
 */
class OverridingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private $additionalCampaignConfiguration;
	private $originalLoader;

	public function __construct( CampaignConfigurationLoaderInterface $originalLoader, array $additionalCampaignConfiguration ) {
		$this->additionalCampaignConfiguration = $additionalCampaignConfiguration;
		$this->originalLoader = $originalLoader;
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		return array_replace_recursive(
			$this->originalLoader->loadCampaignConfiguration( ...$configFiles ),
			$this->additionalCampaignConfiguration
		);
	}


}