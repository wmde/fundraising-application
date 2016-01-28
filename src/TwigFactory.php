<?php


namespace WMDE\Fundraising\Frontend;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Lexer;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use WMDE\Fundraising\Frontend\Presenters\Content\WikiContentProvider;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigFactory {

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
		if ( empty( $this->config['loaders']['wiki'] ) ) {
			return null;
		}
		return new TwigPageLoader( $provider );
	}

	public function newFileSystemLoader() {
		if ( empty( $this->config['loaders']['filesystem'] ) ) {
			return null;
		}
		if ( empty( $this->config['loaders']['filesystem']['template-dir'] ) ) {
			$templateDir = [ 'app/templates' ];
		}
		elseif( is_string( $this->config['loaders']['filesystem']['template-dir'] ) ) {
			$templateDir = [ $this->config['loaders']['filesystem']['template-dir'] ];
		}
		elseif( is_array( $this->config['loaders']['filesystem']['template-dir'] ) ) {
			$templateDir = $this->config['loaders']['filesystem']['template-dir'];
		}
		else {
			throw new \RuntimeException( 'wrong template dir type' );
		}
		$appRoot = realpath( __DIR__ . '/..' ) . '/';
		$templateDir = array_map( function( $dir ) use ( $appRoot ) {
			if ( strlen( $dir ) == 0 || $dir{0} != '/' ) {
				return $appRoot . $dir;
			}
			return $dir;
		}, $templateDir );
		return new Twig_Loader_Filesystem( $templateDir );
	}

	public function newArrayLoader() {
		$templates = $this->config['loaders']['array'] ?? [];
		return new Twig_Loader_Array( $templates );
	}
}
