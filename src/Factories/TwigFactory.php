<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Lexer;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use WMDE\Fundraising\Frontend\Presentation\Content\TwigPageLoader;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\PageRetriever\PageRetriever;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TwigFactory {

	const DEFAULT_TEMPLATE_DIR = 'app/templates';

	private $twig;
	private $config;
	private $cachePath;

	public function __construct( Twig_Environment $twig, array $config, string $cachePath ) {
		$this->twig = $twig;
		$this->config = $config;
		$this->cachePath = $cachePath;
	}

	public function create( array $loaders, array $extensions, array $filters ): Twig_Environment {
		$this->twig->setLoader( new \Twig_Loader_Chain( $loaders ) );

		foreach ( $filters as $filter ) {
			$this->twig->addFilter( $filter );
		}

		foreach ( $extensions as $ext ) {
			$this->twig->addExtension( $ext );
		}

		if ( $this->config['enable-cache'] ) {
			$this->twig->setCache( $this->cachePath );
		}

		if ( isset( $this->config['strict-variables'] ) && $this->config['strict-variables'] === true ) {
			$this->twig->enableStrictVariables();
		} else {
			$this->twig->disableStrictVariables();
		}

		$this->twig->setLexer( new Twig_Lexer( $this->twig, [
			'tag_comment'   => [ '{#', '#}' ],
			'tag_block'     => [ '{%', '%}' ],
			'tag_variable'  => [ '{$', '$}' ]
		] ) );

		return $this->twig;
	}

	public function newWikiPageLoader( PageRetriever $rawPageRetriever, PageRetriever $renderedPageRetriever ) {
		if ( !$this->config['loaders']['wiki']['enabled'] ) {
			return null;
		}
		return new TwigPageLoader( $rawPageRetriever, $renderedPageRetriever, $this->config['loaders']['wiki']['rawpages'] ?? [] );
	}

	public function newFileSystemLoader() {
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
	private function getTemplateDir( $config ): array {
		if ( empty( $config['template-dir'] ) ) {
			$templateDir = [ self::DEFAULT_TEMPLATE_DIR ];
		}
		elseif( is_string( $config['template-dir'] ) ) {
			$templateDir = [ $config['template-dir'] ];
		}
		elseif( is_array( $config['template-dir'] ) ) {
			$templateDir = $config['template-dir'];
		}
		else {
			throw new \RuntimeException( 'wrong template directory type' );
		}
		$appRoot = realpath( __DIR__ . '/../..' ) . '/';
		return $this->convertToAbsolute( $appRoot, $templateDir );
	}

	private function convertToAbsolute( $root, array $dirs ): array {
		return array_map(
			function( $dir ) use ( $root ) {
				if ( strlen( $dir ) == 0 || $dir{0} != '/' ) {
					return $root . $dir;
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
		return new \Twig_SimpleFilter( 'prefix_file', [ $filePrefixer, 'prefixFile' ] );
	}
}
