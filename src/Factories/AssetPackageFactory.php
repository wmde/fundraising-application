<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class AssetPackageFactory {

	public function __construct( private string $externalSkinAssetsUrl ) {
	}

	public function newAssetPackages(): Packages {
		// TODO use manifest instead of EmptyVersionStrategy
		$skinPackage = new PathPackage( '/skins/laika/', new EmptyVersionStrategy() );
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
}
