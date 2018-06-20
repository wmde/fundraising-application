<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

use DateTime;
use DateTimeZone;

/**
 * @license GNU GPL v2+
 */
class CampaignBuilder {

	private $timezone;
	private $utc;

	public function __construct( DateTimeZone $timezone ) {
		$this->timezone = $timezone;
		$this->utc = new DateTimeZone( 'UTC' );
	}

	public function getCampaigns( array $campaignConfig ): array {
		$campaigns = [];
		foreach( $campaignConfig as $name => $config ) {
			$campaign = new Campaign(
				$name,
				$config['url_key'],
				$this->newDate( $config['start'] ),
				$this->newDate( $config['end'] ),
				$config['active']
			);
			foreach ( $config['buckets'] as $bucketName ) {
				$campaign->addBucket( new Bucket( $bucketName, $campaign, $bucketName === $config['default_bucket'] ) );
			}

			$campaigns[] = $campaign;
		}
		return $campaigns;
	}

	private function newDate( string $time ): DateTime {
		$date = new \DateTime( $time, $this->timezone );
		$date->setTimezone( $this->utc );
		return $date;
	}
}