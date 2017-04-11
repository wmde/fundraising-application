<?php

declare( strict_types=1 );

namespace Unit\Factories;

use WMDE\Fundraising\Frontend\Factories\TwigFactory;
use Twig_Loader_Filesystem;
use PHPUnit\Framework\TestCase;

class TwigFactoryTest extends TestCase {

	public function testNewFilesystemLoaderCreatesInstance(): void {
		$factory = new TwigFactory(
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => __DIR__ . '/../../templates'
					]
				]
			],
			'/tmp',
			'de_DE'
		);

		$loader = $factory->newFileSystemLoader();
		$this->assertInstanceOf( Twig_Loader_Filesystem::class, $loader );
		$this->assertSame( [__DIR__ . '/../../templates'], $loader->getPaths() );
	}

	public function testNewFilesystemLoaderUnconfigured_returnsNoInstance(): void {
		$factory = new TwigFactory(
			[
				'loaders' => [
					'filesystem' => [
					]
				]
			],
			'/tmp',
			'de_DE'
		);

		$this->assertNull( $factory->newFileSystemLoader() );
	}

	public function testFilesystemLoaderPrependsRelativePathsToArray(): void {
		$factory = new TwigFactory(
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => 'tests/templates'
					]
				]
			],
			'/tmp',
			'de_DE'
		);
		$loader = $factory->newFileSystemLoader();
		$this->assertInstanceOf( Twig_Loader_Filesystem::class, $loader );
		$realPath = realpath( $loader->getPaths()[0] );
		$this->assertFalse( $realPath === false, 'path does not exist' );
		$this->assertSame( $realPath, realpath( __DIR__ . '/../../templates' ) );
	}

	public function testLocalePlaceholderIsBeingReplaced(): void {
		$factory = new TwigFactory(
			[
				'enable-cache' => false,
				'loaders' => [
					'filesystem' => [
						'template-dir' => [
							'tests/templates',
							'tests/templates/%_locale_%/pages'
						]
					]
				]
			],
			'/tmp',
			'de_DE'
		);

		$loaders = $factory->newFileSystemLoader();
		$this->assertSame(
			[
				realpath( __DIR__ . '/../../templates' ),
				realpath( __DIR__ . '/../../templates/de_DE/pages' )
			],
			$loaders->getPaths()
		);
	}
}
