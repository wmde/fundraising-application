<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_Loader_Array;
use Twig_LoaderInterface;
use WMDE\Fundraising\Frontend\Factories\TwigEnvironmentConfigurator;

/**
 * @covers WMDE\Fundraising\Frontend\Factories\TwigEnvironmentConfigurator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigEnvironmentConfiguratorTest extends \PHPUnit\Framework\TestCase {

	private const LOCALE = 'de_DE';

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$factory = $this->newTwigEnvironmentConfigurator(
			[
				'enable-cache' => false,
			]
		);

		$loader = new Twig_Loader_Array( [ 'variableReplacement.twig' => '{$ testvar $}' ] );

		$twig = $factory->getEnvironment( $this->newTwigEnvironment(), [ $loader ], [], [] );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result );
	}

	private function newTwigEnvironmentConfigurator( array $config ): TwigEnvironmentConfigurator {
		return new TwigEnvironmentConfigurator(
			$config,
			'/tmp/fun',
			self::LOCALE
		);
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

		$factory = $this->newTwigEnvironmentConfigurator( [ 'enable-cache' => false ] );
		$twig = $factory->getEnvironment( $this->newTwigEnvironment(), [ $firstLoader, $secondLoader, $thirdLoader ], [], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}






	private function newTwigEnvironment(): Twig_Environment {
		return new Twig_Environment();
	}


}
