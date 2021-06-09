<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BannerImpressionData\Domain;

class BannerToPageNameMap {

	private ?int $id;
	private string $bannerName;
	private string $pageName;

	public function __construct( string $bannerName, string $pageName ) {
		$this->bannerName = $bannerName;
		$this->pageName = $pageName;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getBannerName(): string {
		return $this->bannerName;
	}

	public function getPageName(): string {
		return $this->pageName;
	}
}
