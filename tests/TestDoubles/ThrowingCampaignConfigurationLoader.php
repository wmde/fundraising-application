<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoaderInterface;

/**
 * @license GPL-2.0-or-later
 */
class ThrowingCampaignConfigurationLoader implements CampaignConfigurationLoaderInterface {

	private $exception;

	public function __construct(
		\Throwable $exception
	) {
		$this->exception = $exception;
	}

	public function loadCampaignConfiguration( string ...$configFiles ): array {
		throw $this->exception;
	}

}
