<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * @license GPL-2.0-or-later
 */
interface CampaignConfigurationLoaderInterface {
	public function loadCampaignConfiguration( string ...$configFiles ): array;
}
