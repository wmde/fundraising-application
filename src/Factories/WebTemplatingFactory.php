<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Packages;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WMDE\Fundraising\ContentProvider\ContentProvider;

class WebTemplatingFactory extends TwigFactory {

	private Packages $packages;

	/**
	 * @param array<string, mixed> $config
	 * @param string $cachePath
	 * @param array<string, string> $translations
	 * @param ContentProvider $contentProvider
	 * @param Packages $assetPackages
	 */
	public function __construct(
		array $config,
		string $cachePath,
		private readonly array $translations,
		private readonly ContentProvider $contentProvider,
		Packages $assetPackages
	) {
		parent::__construct( $config, $cachePath );
		$this->packages = $assetPackages;
	}

	/**
	 * @param array<string, mixed> $globals
	 */
	public function newTemplatingEnvironment( array $globals ): Environment {
		return $this->newTwigEnvironment( $globals );
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getFunctions(): array {
		return [
			new TwigFunction(
				'web_content',
				function ( string $name, array $context = [] ): string {
					return $this->contentProvider->getWeb( $name, $context );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
			new TwigFunction(
				'translations',
				function (): string {
					// "We know the translations come from JSON so we can let the JSON Exception be unchecked because that should never happen
					return json_encode( $this->translations, JSON_THROW_ON_ERROR );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
			new TwigFunction(
				'page_title',
				function ( string $key ): string {
					$title = $this->translations[ 'site_name' ] ?? '';
					return str_replace( '{pageTitle}', $this->translations[ $key ] ?? '', $title );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
		];
	}

	/**
	 * @return AbstractExtension[]
	 */
	protected function getExtensions(): array {
		return [
			new AssetExtension( $this->packages )
		];
	}

}
