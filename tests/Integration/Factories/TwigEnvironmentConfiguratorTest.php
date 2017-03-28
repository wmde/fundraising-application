<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_LoaderInterface;
use WMDE\Fundraising\Frontend\Factories\TwigEnvironmentConfigurator;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

/**
 * @covers WMDE\Fundraising\Frontend\Factories\TwigEnvironmentConfigurator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigEnvironmentConfiguratorTest extends \PHPUnit\Framework\TestCase {

	private const LOCALE = 'de_DE';

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
			[
				'enable-cache' => false,
				'loaders' => [
					'array' => [ 'variableReplacement.twig' => '{$ testvar $}' ]
				]
			],
			'/tmp/fun',
			self::LOCALE
		);
		$twig = $factory->getEnvironment( [ $factory->newArrayLoader() ], [], [] );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result );
	}

	public function testTwigInstancesTryAllLoadersUntilTemplateIsFound() {
		$loaderException = new Twig_Error_Loader( 'not found' );
		$firstLoader = $this->createMock( Twig_LoaderInterface::class );
		$firstLoader->method( 'getSource' )->willThrowException( $loaderException );
		$firstLoader->method( 'isFresh' )->willThrowException( $loaderException );
		$firstLoader->method( 'getCacheKey' )->willThrowException( $loaderException );

		$secondLoader = $this->createMock( Twig_LoaderInterface::class );
		$secondLoader->method( 'getSource' )->willReturn( 'Meeow!' );
		$secondLoader->method( 'isFresh' )->willReturn( true );
		$secondLoader->method( 'getCacheKey' )->willReturn( 'Canis_silvestris' );

		$thirdLoader = $this->createMock( Twig_LoaderInterface::class );
		$thirdLoader->expects( $this->never() )->method( $this->anything() );

		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
			[ 'enable-cache' => false ],
			'/tmp/fun',
			self::LOCALE
		);
		$twig = $factory->getEnvironment( [ $firstLoader, $secondLoader, $thirdLoader ], [], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}

	public function testFilesystemLoaderConvertsStringPathToArray() {
		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => __DIR__ . '/../../templates'
					]
				]
			],
			'/tmp/fun',
			self::LOCALE
		);
		$loader = $factory->newFileSystemLoader();
		$this->assertSame( [ __DIR__ . '/../../templates' ], $loader->getPaths() );
	}

	public function testFilesystemLoaderPrependsRelativePathsToArray() {
		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => 'tests/templates'
					]
				]
			],
			'/tmp/fun',
			self::LOCALE
		);
		$loader = $factory->newFileSystemLoader();
		$realPath = realpath( $loader->getPaths()[0] );
		$this->assertFalse( $realPath === false, 'path does not exist' );
		$this->assertSame( $realPath, realpath( __DIR__ . '/../../templates' ) );
	}

	public function testFilePrefixerIsCalledInTemplate() {
		$prefixer = $this->getMockBuilder( FilePrefixer::class )->disableOriginalConstructor()->getMock();
		$prefixer->expects( $this->once() )
			->method( 'prefixFile' )
			->willReturn( 'baaaaad.testfile.js' )
			->with( 'testfile.js' );

		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
			[
				'enable-cache' => false,
				'loaders' => [
					'array' => [ 'filePrefix.twig' => '{$ "testfile.js"|prefix_file $}' ]
				]
			],
			'/tmp/fun',
			self::LOCALE
		);

		$filters = [ $factory->newFilePrefixFilter( $prefixer ) ];
		$twig = $factory->getEnvironment( [ $factory->newArrayLoader() ], [], $filters );
		$result = $twig->render( 'filePrefix.twig' );
		$this->assertSame( 'baaaaad.testfile.js', $result );
	}

	private function newTwigEnvironment(): Twig_Environment {
		return new Twig_Environment();
	}

	public function testLocalePlaceholderIsBeingReplaced() {
		$expectedPaths = [
			realpath( __DIR__ . '/../../templates' ),
			realpath( __DIR__ . '/../../templates/' . self::LOCALE . '/pages' )
		];
		$factory = new TwigEnvironmentConfigurator(
			$this->newTwigEnvironment(),
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
			'/tmp/fun',
			self::LOCALE
		);

		$loaders = $factory->newFileSystemLoader();
		$this->assertSame( $expectedPaths, $loaders->getPaths() );
	}

}
