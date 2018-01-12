<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_LoaderInterface;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\Fundraising\Frontend\Presentation\TwigEnvironmentConfigurator;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\ContentProvider\ContentException;
use WMDE\Fundraising\ContentProvider\ContentProvider;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\TwigEnvironmentConfigurator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigEnvironmentConfiguratorTest extends TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables(): void {
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
			$factory->getLayoutTemplate( 'variableReplacement.twig' )->render( ['testvar' => 'Meeow!'] )
		);
	}

	public function testTwigInstancesTryAllLoadersUntilTemplateIsFound(): void {
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
			'/tmp/fun'
		);
		$twig = $environmentConfigurator->getEnvironment( $this->newTwigEnvironment(), [$firstLoader, $secondLoader, $thirdLoader], [], [] );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result );
	}

	private function newTwigEnvironment(): Twig_Environment {
		return new Twig_Environment();
	}

	public function testWhenWebBasePathIsEmpty_templatedPathsReferToRootPath(): void {
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

	public function testWhenWebBasePathIsNotEmpty_templatedPathsReferToRootPath(): void {
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

	public function testFilePrefixerIsCalledInTemplate(): void {
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

	public function testSandboxedWebContentExists_isReturnedAndContextPassed(): void {
		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '<p>{$ web_content("lorem", { "state": "fine" }) $}</p>',
						'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
						'lorem.twig' => 'More Dragons!'
					]
				]
			]
		] )->getFactory();

		$provider = $this->createMock( ContentProvider::class );
		$provider->method( 'getWeb' )
			->with( 'lorem', ['state' => 'fine'] )
			->willReturn( 'ipsum. all is <strong>fine</strong>.' );

		$factory->setContentProvider( $provider );

		$this->assertSame(
			'<p>ipsum. all is <strong>fine</strong>.</p>',
			$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [] )
		);
	}

	public function testSandboxedMailContentExists_isReturnedAndContextPassed(): void {
		$factory = TestEnvironment::newInstance( [
			'mailer-twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '{$ mail_content("something", { "dontation_id": 45 }) $} end',
						'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
						'lorem.twig' => 'More Dragons!'
					]
				]
			]
		] )->getFactory();

		$provider = $this->createMock( ContentProvider::class );
		$provider->method( 'getMail' )
			->with( 'something', [ 'dontation_id' => 45 ] )
			->willReturn( 'you got mail' );

		$factory->setContentProvider( $provider );

		$this->assertSame(
			'you got mail end',
			$factory->getMailerTemplate( 'template_with_content.twig' )->render( [] )
		);
	}

	public function testSandboxedWebContentDoesntExist_exceptionIsThrown(): void {

		$factory = TestEnvironment::newInstance( [
			'twig' => [
				'loaders' => [
					'array' => [
						'template_with_content.twig' => '{$ web_content("lorem") $}',
					]
				]
			]
		] )->getFactory();

		$provider = $this->createMock( ContentProvider::class );
		$provider->method( 'getWeb' )
			->with( 'lorem' )
			->willThrowException( new ContentException( "An exception occured rendering 'lorem'" ) );

		$factory->setContentProvider( $provider );

		$this->expectException( Twig_Error_Runtime::class );
		$this->expectExceptionMessageRegExp( '/An exception occured rendering \'lorem\'/' );

		$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [] );
	}
}
