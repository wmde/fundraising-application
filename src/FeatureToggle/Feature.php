<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\FeatureToggle;

class Feature {
	public function __construct(
		public readonly string $name,
		public readonly bool $active
	) {
	}

	public function getId(): string {
		return "features.{$this->name}";
	}
}
