<?php


namespace WMDE\Fundraising\Frontend;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Lexer;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use WMDE\Fundraising\Frontend\Presentation\Content\WikiContentProvider;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigFactory {

	const DEFAULT_TEMPLATE_DIR = 'app/templates';

	private $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function create( array $loaders ): Twig_Environment {
		$options = [];

		if ( $this->config['enable-cache'] ) {
			$options['cache'] = __DIR__ . '/../app/cache';
		}

		$loader = new \Twig_Loader_Chain( $loaders );

		$twig = new Twig_Environment(
			$loader,
			$options
		);

		$twig->addExtension( new Twig_Extension_StringLoader() );

		$lexer = new Twig_Lexer( $twig, [
			'tag_comment'   => [ '{#', '#}' ],
			'tag_block'     => [ '{%', '%}' ],
			'tag_variable'  => [ '{$', '$}' ]
		] );
		$twig->setLexer( $lexer );

		return $twig;
	}

	public function newWikiPageLoader( WikiContentProvider $provider ) {
		if ( !$this->config['loaders']['wiki']['enabled'] ) {
			return null;
		}
		return new TwigPageLoader( $provider, $this->config['loaders']['wiki']['rawpages'] ?? [] );
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
		$appRoot = realpath( __DIR__ . '/..' ) . '/';
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
}
