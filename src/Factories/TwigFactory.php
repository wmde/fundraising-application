<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use RuntimeException;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension_StringLoader;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use WMDE\Fundraising\Frontend\Presentation\TwigEnvironmentConfigurator;

class TwigFactory {

	private $config;
	private $cachePath;
	private $locale;

	public function __construct( array $config, string $cachePath, string $locale ) {
		$this->config = $config;
		$this->cachePath = $cachePath;
		$this->locale = $locale;
	}

	public function newFileSystemLoader(): ?Twig_Loader_Filesystem {
		if ( empty( $this->config['loaders']['filesystem'] ) ) {
			return null;
		}
		$templateDir = $this->getTemplateDir( $this->config['loaders']['filesystem'] );
		return new Twig_Loader_Filesystem( $templateDir );
	}

	/**
	 * Create an array of absolute template directories from the loader
	 *
	 * @param array $config Configuration for the filesystem loader. The key 'template-dir' can be a string or an array.
	 * @return array
	 */
	private function getTemplateDir( array $config ): array {
		if ( is_string( $config['template-dir'] ) ) {
			$templateDir = [ $config['template-dir'] ];
		}
		elseif ( is_array( $config['template-dir'] ) ) {
			$templateDir = $config['template-dir'];
		}
		else {
			throw new RuntimeException( 'wrong template directory type' );
		}
		$appRoot = realpath( __DIR__ . '/../..' ) . '/';
		return $this->convertToAbsolute( $appRoot, $templateDir );
	}

	private function convertToAbsolute( $root, array $dirs ): array {
		return array_map(
				function( $dir ) use ( $root ) {
					if ( strlen( $dir ) == 0 || $dir{0} != '/' ) {
						$dir = $root . $dir;
					}
					return $dir;
				},
				$dirs
		);
	}

	public function newArrayLoader() {
		$templates = $this->config['loaders']['array'] ?? [];
		return new Twig_Loader_Array( $templates );
	}

	public function newStringLoaderExtension() {
		return new Twig_Extension_StringLoader();
	}

	public function newTranslationExtension( TranslatorInterface $translator ) {
		return new TranslationExtension( $translator );
	}

	public function newFilePrefixFilter( FilePrefixer $filePrefixer ) {
		return new Twig_SimpleFilter( 'prefix_file', [ $filePrefixer, 'prefixFile' ] );
	}

	public function newTwigEnvironmentConfigurator() {
		return new TwigEnvironmentConfigurator( $this->config, $this->cachePath );
	}
}