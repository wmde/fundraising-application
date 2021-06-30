<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class AssetPackageFactory {
	public function newAssetPackages(): Packages {
		return new Packages(
			new PathPackage( '/res/', new EmptyVersionStrategy() ),
			[
				'skin' => new PathPackage( '/skins/laika/', new EmptyVersionStrategy() )
			]
		);
	}
}
