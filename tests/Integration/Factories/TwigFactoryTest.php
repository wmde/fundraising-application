<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_LoaderInterface;
use WMDE\Fundraising\Frontend\Factories\TwigFactory;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

/**
 * @covers WMDE\Fundraising\Frontend\Factories\TwigFactory
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$factory = new TwigFactory(
			$this->newTwigEnvironment(),
			[
				'enable-cache' => false,
				'loaders' => [
					'array' => [ 'variableReplacement.twig' => '{$ testvar $}' ]
				]
			],
			'/tmp/fun'
		);
		$twig = $factory->create( [ $factory->newArrayLoader() ], [], [] );
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

		$factory = new TwigFactory( $this->newTwigEnvironment(), [ 'enable-cache' => false ], '/tmp/fun' );
		$twig = $factory->create( [ $firstLoader, $secondLoader, $thirdLoader ], [], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}

	public function testFilesystemLoaderConvertsStringPathToArray() {
		$factory = new TwigFactory(
			$this->newTwigEnvironment(),
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => __DIR__ . '/../../templates'
					]
				]
			],
			'/tmp/fun' );
		$loader = $factory->newFileSystemLoader();
		$this->assertSame( [ __DIR__ . '/../../templates' ], $loader->getPaths() );
	}

	public function testFilesystemLoaderPrependsRelativePathsToArray() {
		$factory = new TwigFactory(
			$this->newTwigEnvironment(),
			[
				'loaders' => [
					'filesystem' => [
						'template-dir' => 'tests/templates'
					]
				]
			],
			'/tmp/fun'
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

		$factory = new TwigFactory(
			$this->newTwigEnvironment(),
			[
				'enable-cache' => false,
				'loaders' => [
					'array' => [ 'filePrefix.twig' => '{$ "testfile.js"|prefix_file $}' ]
				]
			],
			'/tmp/fun'
		);

		$filters = [ $factory->newFilePrefixFilter( $prefixer ) ];
		$twig = $factory->create( [ $factory->newArrayLoader() ], [], $filters );
		$result = $twig->render( 'filePrefix.twig' );
		$this->assertSame( 'baaaaad.testfile.js', $result );
	}

	private function newTwigEnvironment(): Twig_Environment {
		return new Twig_Environment();
	}
}
