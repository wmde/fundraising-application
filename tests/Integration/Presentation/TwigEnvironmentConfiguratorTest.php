<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Loader_Array;
use Twig_LoaderInterface;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\Fundraising\Frontend\Presentation\TwigEnvironmentConfigurator;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\TwigEnvironmentConfigurator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigEnvironmentConfiguratorTest extends TestCase {

	private const LOCALE = 'de_DE';

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'variableReplacement.twig' => '{$ testvar $}'
					]
				]
			]
		] )->getFactory();

		$this->assertSame(
			'Meeow!',
			$factory->getLayoutTemplate( 'variableReplacement.twig' )->render( [ 'testvar' => 'Meeow!' ] )
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

		$environmentConfigurator = new TwigEnvironmentConfigurator(
			[
				'enable-cache' => false,
				'web-basepath' => ''
			],
			'/tmp/fun',
			self::LOCALE
		);
		$twig = $environmentConfigurator->getEnvironment( $this->newTwigEnvironment(), [ $firstLoader, $secondLoader, $thirdLoader ], [], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}

	private function newTwigEnvironment(): Twig_Environment {
		return new Twig_Environment();
	}

	public function testWhenWebBasePathIsEmpty_templatedPathsReferToRootPath() {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css">'
					]
				]
			],
			'web-basepath' => ''
		] )->getFactory();

		$this->assertSame(
			'<style src="/someFile.css">',
			$factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] )
		);
	}

	public function testWhenWebBasePathIsNotEmpty_templatedPathsReferToRootPath() {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css">'
					]
				]
			],
			'web-basepath' => '/mydevsubdir'
		] )->getFactory();

		$this->assertSame(
			'<style src="/mydevsubdir/someFile.css">',
			$factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] )
		);
	}

	public function testFilePrefixerIsCalledInTemplate() {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'unicorns.html.twig' => '{$ "testfile.js"|prefix_file $}'
					]
				]
			]
		] )->getFactory();

		$prefixer = new FilePrefixer( 'mylittleprefix' );
		$factory->setFilePrefixer( $prefixer );

		$this->assertContains(
			'mylittleprefix.testfile.js',
			$factory->getLayoutTemplate( 'unicorns.html.twig' )->render( [] )
		);
	}

	public function testSandboxedContentExists_isReturned(): void {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '<p>{$ sandboxed_content("lorem") $}</p>',
						'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
						'lorem.twig' => 'More Dragons!'
					]
				]
			]
		] )->getFactory();

		$factory->setContentPageTemplateLoader(
			new Twig_Loader_Array( [
				'lorem.twig' => 'ipsum. all is <strong>fine</strong>.'
			] )
		);

		$this->assertSame(
			'<p>ipsum. all is <strong>fine</strong>.</p>',
			$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [ ] )
		);
	}

	public function testSandboxedContentExists_isReturnedAndContextInterpolated(): void {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '{$ sandboxed_content("lorem", { "user": "cat<br>"}) $}',
					]
				]
			]
		] )->getFactory();

		$factory->setContentPageTemplateLoader(
			new Twig_Loader_Array( [
				'lorem.twig' => 'Willkommen +{$ dummy $}+ {$ user $}.'
			] )
		);

		$this->assertSame(
			'Willkommen ++ cat&lt;br&gt;.',
			$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [
				'dummy' => 'not available in sandbox'
			] ),
			"expected main twig's var not filled, cat interpolated but html encoded"
		);
	}

	public function testSandboxedContentDoesntExist_exceptionIsThrown(): void {

		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '{$ sandboxed_content("lorem") $}',
					]
				]
			]
		] )->getFactory();

		$this->expectException( Twig_Error_Runtime::class );
		$this->expectExceptionMessageRegExp('/Template for page \'lorem\' not found/');

		$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [ ] );
	}
}
