<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig\Cache\CacheInterface;
use Twig\Cache\FilesystemCache;
use Twig\Cache\NullCache;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

abstract class TwigFactory {

	private array $config;
	private string $cachePath;
	private ?CacheInterface $cache;

	public function __construct( array $config, string $cachePath ) {
		$this->config = $config;
		$this->cachePath = $cachePath;
		$this->cache = null;
	}

	private function getLoader(): LoaderInterface {
		if ( !empty( $this->config['loaders']['filesystem'] ) ) {
			return new FilesystemLoader( $this->config['loaders']['filesystem'] );
		}
		throw new \UnexpectedValueException( 'Invalid Twig loader configuration - missing filesystem' );
	}

	protected function newTwigEnvironment( array $globals = [] ): Environment {
		$options = [
			'strict_variables' => isset( $this->config['strict-variables'] ) && $this->config['strict-variables'] === true,
			'cache' => $this->getCache()
		];
		$twig = new Environment( $this->getLoader(), $options );

		foreach ( $globals as $name => $global ) {
			$twig->addGlobal( $name, $global );
		}

		foreach ( $this->getExtensions() as $extension ) {
			$twig->addExtension( $extension );
		}

		foreach ( $this->getFunctions() as $function ) {
			$twig->addFunction( $function );
		}

		foreach ( $this->getFilters() as $filter ) {
			$twig->addFilter( $filter );
		}

		$twig->setLexer( new Lexer( $twig, [
			'tag_comment' => [ '{#', '#}' ],
			'tag_block' => [ '{%', '%}' ],
			'tag_variable' => [ '{$', '$}' ]
		] ) );

		return $twig;
	}

	public function getCache(): CacheInterface {
		if ( empty( $this->config['enable-cache'] ) ) {
			return new NullCache();
		}
		if ( $this->cache === null ) {
			$this->cache = new FilesystemCache( $this->cachePath );
		}
		return $this->cache;
	}

	/**
	 * @return TwigFilter[]
	 */
	protected function getFilters(): array {
		return [];
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getFunctions(): array {
		return [];
	}

	/**
	 * @return AbstractExtension[]
	 */
	protected function getExtensions(): array {
		return [];
	}

}
