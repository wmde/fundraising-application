<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

class Bucket {

	public const DEFAULT = true;
	public const NON_DEFAULT = false;

	public function __construct(
		private readonly string $name,
		private readonly Campaign $campaign,
		private readonly bool $defaultBucket
	) {
	}

	public function getName(): string {
		return $this->name;
	}

	public function getId(): string {
		return 'campaigns.' . $this->getCampaign()->getName() . '.' . $this->getName();
	}

	public function getCampaign(): Campaign {
		return $this->campaign;
	}

	public function isDefaultBucket(): bool {
		return $this->defaultBucket;
	}

	/**
	 * @return array<string, int>
	 */
	public function getParameters(): array {
		return [ $this->campaign->getUrlKey() => $this->campaign->getIndexByBucket( $this ) ];
	}

}
