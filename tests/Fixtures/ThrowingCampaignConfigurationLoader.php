<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;

class ThrowingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	public function __construct( private readonly \Throwable $exception ) {
	}

	/**
	 * @return CampaignConfiguration[]
	 */
	public function loadCampaignConfiguration( string ...$configFiles ): array {
		throw $this->exception;
	}

}
