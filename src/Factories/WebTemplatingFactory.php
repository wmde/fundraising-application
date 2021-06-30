<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

class WebTemplatingFactory extends TwigFactory {

	private array $translations;
	private ContentProvider $contentProvider;
	private FilePrefixer $filePrefixer;

	public function __construct( array $config, string $cachePath, array $translations, ContentProvider $contentProvider, FilePrefixer $filePrefixer ) {
		parent::__construct( $config, $cachePath );
		$this->translations = $translations;
		$this->contentProvider = $contentProvider;
		$this->filePrefixer = $filePrefixer;
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
			),
			new TwigFunction(
				'translations',
				function (): string {
					return json_encode( $this->translations );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
		];
	}

	protected function getFilters(): array {
		return [
			new TwigFilter( 'prefix_file', [ $this->filePrefixer, 'prefixFile' ] )
		];
	}

}
