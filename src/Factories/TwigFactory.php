<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFilter;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

abstract class TwigFactory {

	private array $config;
	private string $cachePath;
	private string $locale;

	public function __construct( array $config, string $cachePath, string $locale ) {
		$this->config = $config;
		$this->cachePath = $cachePath;
		$this->locale = $locale;
	}

	protected function newFilePrefixFilter( FilePrefixer $filePrefixer ): TwigFilter {
		return new TwigFilter( 'prefix_file', [ $filePrefixer, 'prefixFile' ] );
	}

	private function getLoader(): LoaderInterface {
		if ( !empty( $this->config['loaders']['filesystem'] ) ) {
			return new FilesystemLoader( $this->config['loaders']['filesystem'] );
		}
		throw new \UnexpectedValueException( 'Invalid Twig loader configuration - missing filesystem' );
	}

	protected function newTwigEnvironment( array $filters, array $functions, array $globals = [] ): Environment {
		$options = [
			'strict_variables' => isset( $this->config['strict-variables'] ) && $this->config['strict-variables'] === true,
			'cache' => empty( $this->config['enable-cache'] ) ? false : $this->config['enable-cache']
		];
		$twig = new Environment( $this->getLoader(), $options );

		foreach ( $globals as $name => $global ) {
			$twig->addGlobal( $name, $global );
		}

		foreach ( $functions as $function ) {
			$twig->addFunction( $function );
		}

		foreach ( $filters as $filter ) {
			$twig->addFilter( $filter );
		}

		$twig->setLexer( new Lexer( $twig, [
			'tag_comment' => [ '{#', '#}' ],
			'tag_block' => [ '{%', '%}' ],
			'tag_variable' => [ '{$', '$}' ]
		] ) );

		return $twig;
	}

}
