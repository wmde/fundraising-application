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
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TwigEnvironmentConfigurator {

	private $config;
	private $cachePath;

	public function __construct( array $config, string $cachePath ) {
		$this->config = $config;
		$this->cachePath = $cachePath;
	}

	public function getEnvironment( Twig_Environment $twig, array $loaders, array $extensions, array $filters ): Twig_Environment {
		$twig->setLoader( new \Twig_Loader_Chain( $loaders ) );

		foreach ( $filters as $filter ) {
			$twig->addFilter( $filter );
		}

		foreach ( $extensions as $ext ) {
			$twig->addExtension( $ext );
		}

		if ( $this->config['enable-cache'] ) {
			$twig->setCache( $this->cachePath );
		}

		if ( isset( $this->config['strict-variables'] ) && $this->config['strict-variables'] === true ) {
			$twig->enableStrictVariables();
		} else {
			$twig->disableStrictVariables();
		}

		$twig->setLexer( new Twig_Lexer( $twig, [
			'tag_comment'   => [ '{#', '#}' ],
			'tag_block'     => [ '{%', '%}' ],
			'tag_variable'  => [ '{$', '$}' ]
		] ) );

		return $twig;
	}
}
