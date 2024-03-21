<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use ArrayIterator;
use IteratorAggregate;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

/**
 * @implements IteratorAggregate<Campaign>
 */
class CampaignCollection implements IteratorAggregate {

	/** @var Campaign[] */
	private array $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	/**
	 * Select the most distant active campaign where the end date is not in the past
	 * @return null|Campaign
	 */
	public function getMostDistantCampaign(): ?Campaign {
		$now = new \DateTime();
		return array_reduce( $this->campaigns, static function ( ?Campaign $mostDistant, Campaign $current ) use ( $now ) {
			if ( !$current->isActive() || $current->getEndTimestamp() < $now ) {
				return $mostDistant;
			}
			if ( $mostDistant === null ) {
				return $current;
			}
			return $mostDistant->getEndTimestamp() > $current->getEndTimestamp() ? $mostDistant : $current;
		}, null );
	}

	/**
	 * @return ArrayIterator<Campaign>
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->campaigns );
	}

}
