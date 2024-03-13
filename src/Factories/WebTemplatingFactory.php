<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Packages;
use Twig\Environment;
use Twig\TwigFunction;
use WMDE\Fundraising\ContentProvider\ContentProvider;

class WebTemplatingFactory extends TwigFactory {

	private ContentProvider $contentProvider;
	private Packages $packages;

	public function __construct( array $config, string $cachePath, ContentProvider $contentProvider, Packages $assetPackages ) {
		parent::__construct( $config, $cachePath );
		$this->contentProvider = $contentProvider;
		$this->packages = $assetPackages;
	}

	public function newTemplatingEnvironment( array $globals ): Environment {
		return $this->newTwigEnvironment( $globals );
	}

	protected function getFunctions(): array {
		return [
			new TwigFunction(
				'web_content',
				function ( string $name, array $context = [] ): string {
					return $this->contentProvider->getWeb( $name, $context );
				},
				[ 'is_safe' => [ 'html' ] ]
			)
		];
	}

	protected function getExtensions(): array {
		return [
			new AssetExtension( $this->packages )
		];
	}

}
