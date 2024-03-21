<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class AssetPackageFactory {

	public function __construct(
		private readonly string $applicationEnvironment,
		private readonly string $externalSkinAssetsUrl,
		private readonly string $appRoot
	) {
	}

	public function newAssetPackages(): Packages {
		$skinPackage = new PathPackage( '/skins/laika/', $this->newVersionStrategyForSkin() );
		if ( !empty( $this->externalSkinAssetsUrl ) ) {
			$skinPackage = new UrlPackage( $this->externalSkinAssetsUrl, new EmptyVersionStrategy() );
		}
		return new Packages(
			new PathPackage( '/res/', new EmptyVersionStrategy() ),
			[
				'skin' => $skinPackage
			]
		);
	}

	private function newVersionStrategyForSkin(): VersionStrategyInterface {
		if ( $this->applicationEnvironment === 'test' ) {
			return new EmptyVersionStrategy();
		}
		return new JsonManifestVersionStrategy( $this->appRoot . '/web/skins/laika/manifest.json' );
	}
}
