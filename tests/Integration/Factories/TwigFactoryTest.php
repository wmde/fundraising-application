<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use WMDE\Fundraising\Frontend\Factories\TwigFactory;
use Twig_LoaderInterface;
use Twig_Error_Loader;

/**
 * @covers WMDE\Fundraising\Frontend\Factories\TwigFactory
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$factory = new TwigFactory( [
			'enable-cache' => false,
			'loaders' => [
				'array' => [ 'variableReplacement.twig' => '{$ testvar $}' ]
			]
		] );
		$twig = $factory->create( [ $factory->newArrayLoader() ], [] );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result );
	}

	public function testTwigInstancesTryAllLoadersUntilTemplateIsFound() {
		$loaderException = new Twig_Error_Loader( 'not found' );
		$firstLoader = $this->getMock( Twig_LoaderInterface::class );
		$firstLoader->method( 'getSource' )->willThrowException( $loaderException );
		$firstLoader->method( 'isFresh' )->willThrowException( $loaderException );
		$firstLoader->method( 'getCacheKey' )->willThrowException( $loaderException );

		$secondLoader = $this->getMock( Twig_LoaderInterface::class );
		$secondLoader->method( 'getSource' )->willReturn( 'Meeow!' );
		$secondLoader->method( 'isFresh' )->willReturn( true );
		$secondLoader->method( 'getCacheKey' )->willReturn( 'Canis_silvestris' );

		$thirdLoader = $this->getMock( Twig_LoaderInterface::class );
		$thirdLoader->expects( $this->never() )->method( $this->anything() );

		$factory = new TwigFactory( [ 'enable-cache' => false ] );
		$twig = $factory->create( [ $firstLoader, $secondLoader, $thirdLoader ], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}

	public function testFilesystemLoaderConvertsStringPathToArray() {
		$factory = new TwigFactory( [
			'loaders' => [
				'filesystem' => [
					'template-dir' => __DIR__ . '/../../templates'
				]
			]
		] );
		$loader = $factory->newFileSystemLoader();
		$this->assertSame( [ __DIR__ . '/../../templates' ], $loader->getPaths() );
	}

	public function testFilesystemLoaderPrependsRelativePathsToArray() {
		$factory = new TwigFactory( [
			'loaders' => [
				'filesystem' => [
					'template-dir' => 'tests/templates'
				]
			]
		] );
		$loader = $factory->newFileSystemLoader();
		$realPath = realpath( $loader->getPaths()[0] );
		$this->assertFalse( $realPath === false, 'path does not exist' );
		$this->assertSame( $realPath, realPath( __DIR__ . '/../../templates' ) );
	}

}
