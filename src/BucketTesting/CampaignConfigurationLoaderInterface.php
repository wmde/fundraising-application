<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

interface CampaignConfigurationLoaderInterface {

	/**
	 * @param string ...$configFiles
	 *
	 * @return CampaignConfiguration[]
	 */
	public function loadCampaignConfiguration( string ...$configFiles ): array;
}
