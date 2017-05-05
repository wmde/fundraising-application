<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Twig_Environment;
use Twig_Lexer;

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

	public function getEnvironment( Twig_Environment $twig, array $loaders, array $extensions = [], array $filters = [],
									array $functions = [] ): Twig_Environment {
		$twig->setLoader( new \Twig_Loader_Chain( $loaders ) );

		foreach ( $functions as $function ) {
			$twig->addFunction( $function );
		}

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
			'tag_comment' => ['{#', '#}'],
			'tag_block' => ['{%', '%}'],
			'tag_variable' => ['{$', '$}']
		] ) );

		$this->setDefaultTwigVariables( $twig );

		return $twig;
	}

	private function setDefaultTwigVariables( Twig_Environment $twig ): void {
		$twig->addGlobal( 'basepath', $this->config['web-basepath'] );
	}

}
