<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig\Environment;
use Twig\TwigFunction;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

class WebTemplatingFactory extends TwigFactory {

	public function newTemplatingEnvionment( array $translations, ContentProvider $contentProvider, FilePrefixer $filePrefixer, array $globals ): Environment {
		$filters = [
			$this->newFilePrefixFilter( $filePrefixer )
		];
		$functions = [
			new TwigFunction(
				'web_content',
				static function ( string $name, array $context = [] ) use( $contentProvider ): string {
					return $contentProvider->getWeb( $name, $context );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
			new TwigFunction(
				'translations',
				static function () use ( $translations ): string {
					return json_encode( $translations );
				},
				[ 'is_safe' => [ 'html' ] ]
			),
		];

		return $this->newTwigEnvironment( $filters, $functions, $globals );
	}
}
