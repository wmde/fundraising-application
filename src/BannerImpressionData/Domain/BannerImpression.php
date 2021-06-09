<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BannerImpressionData\Domain;

class BannerImpression {

	private ?int $id;
	private string $bannerName;
	private int $impressionCount;
	private \DateTimeImmutable $intervalStart;

	public function __construct( string $bannerName, int $impressionCount, \DateTimeImmutable $intervalStart ) {
		$this->bannerName = $bannerName;
		$this->impressionCount = $impressionCount;
		$this->intervalStart = $intervalStart;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getBannerName(): string {
		return $this->bannerName;
	}

	public function getImpressionCount(): int {
		return $this->impressionCount;
	}

	public function getIntervalStart(): \DateTimeImmutable {
		return $this->intervalStart;
	}
}
