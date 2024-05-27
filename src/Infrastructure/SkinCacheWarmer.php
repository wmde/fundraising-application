<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Twig\Error\Error;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class SkinCacheWarmer implements CacheWarmerInterface {

	public function __construct( private readonly FunFunFactory $funFunFactory ) {
		$this->funFunFactory->setLocale( 'de_DE' );
	}

	public function isOptional(): bool {
		return false;
	}

	/**
	 * @param string $cacheDir
	 * @param string|null $buildDir
	 *
	 * @return string[]
	 */
	public function warmUp( string $cacheDir, ?string $buildDir = null ): array {
		$templates = $this->getTemplates();

		foreach ( $templates as $template ) {
			try {
				$this->funFunFactory->getSkinTwig()->load( $template );
			} catch ( Error $e ) {
				// problem during compilation, give up
				// might be a syntax error or a non-Twig template
			}
		}

		return $templates;
	}

	private function getTemplates(): array {
		$cacheDirectory = $this->funFunFactory->getSkinDirectory();
		$templates = [];
		$scandir = scandir( $cacheDirectory );
		if ( $scandir === false ) {
			throw new \RuntimeException( 'Failed to list files and directories inside this specified path: ' . $cacheDirectory );
		}
		foreach ( $scandir as $file ) {
			if ( !is_dir( $file ) && str_contains( $file, '.twig' ) ) {
				$templates[] = $file;
			}
		}
		return $templates;
	}
}
