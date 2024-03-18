<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

interface CampaignConfigurationLoaderInterface {
	public function loadCampaignConfiguration( string ...$configFiles ): array;
}
