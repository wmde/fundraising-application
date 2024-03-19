<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use DateTimeZone;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

class CampaignBuilder {

	public function __construct( private readonly DateTimeZone $timezone ) {
	}

	public function getCampaigns( array $campaignConfig ): array {
		$campaigns = [];
		foreach ( $campaignConfig as $name => $config ) {
			$campaign = new Campaign(
				$name,
				$config['url_key'],
				CampaignDate::createFromString( $config['start'], $this->timezone ),
				CampaignDate::createFromString( $config['end'], $this->timezone ),
				$config['active'],
				$config['param_only'] ?? Campaign::NEEDS_NO_URL_KEY
			);
			foreach ( $config['buckets'] as $bucketName ) {
				$campaign->addBucket( new Bucket( $bucketName, $campaign, $bucketName === $config['default_bucket'] ) );
			}

			$campaigns[] = $campaign;
		}
		return $campaigns;
	}
}
