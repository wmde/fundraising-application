<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Error\RuntimeError;
use WMDE\Fundraising\ContentProvider\ContentException;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;
use WMDE\Fundraising\Frontend\Tests\TestEnvironmentBootstrapper;

/**
 * Tests if the FunFunFactory correctly builds Twig environment for HTML
 *
 * @covers \WMDE\Fundraising\Frontend\Factories\WebTemplatingFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\TwigFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\FunFunFactory::getSkinTwig
 * @covers \WMDE\Fundraising\Frontend\Factories\AssetPackageFactory
 *
 * @license GPL-2.0-or-later
 */
class WebTemplatingFactoryTest extends KernelTestCase {

	private const TEMPLATE_DIR = 'templates';

	private function getFactory( array $configOverrides = [] ): FunFunFactory {
		static::bootKernel();
		$bootstrapper = static::getContainer()->get( EnvironmentBootstrapper::class );

		if ( !( $bootstrapper instanceof TestEnvironmentBootstrapper ) ) {
			throw new \LogicException( 'We need to use TestEnvironmentBootstrapper to be able to override the configuration' );
		}

		$bootstrapper->overrideConfiguration( $configOverrides );

		$factory = static::getContainer()->get( FunFunFactory::class );
		$factory->setLocale( 'de_DE' );
		$factory->setSkinDirectory( vfsStream::url( self::TEMPLATE_DIR ) );
		$factory->setSelectedBuckets( [ CampaignFixture::createBucket() ] );
		return $factory;
	}

	public function testFunFunFactoryPassesGlobalsFromConfig(): void {
		$factory = $this->getFactory( [
			'web-basepath' => '/mydevsubdir',
			'assets-path' => 'http://example.com/assets'
		] );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css"><img src="{$ assets_path $}/kitty.jpg" />'
		] );

		$output = $factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] );

		$this->assertSame( '<style src="/mydevsubdir/someFile.css"><img src="http://example.com/assets/kitty.jpg" />', $output );
	}

	public function testAssetsGetRendered(): void {
		$factory = $this->getFactory();
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'unicorns.html.twig' => 'default: {$ asset( "css/styles.css" ) $} skin: {$ asset( "js/main.js", "skin" ) $}'
		] );

		$output = $factory->getLayoutTemplate( 'unicorns.html.twig' )->render( [] );

		$this->assertMatchesRegularExpression( '!/res/css/styles.css!', $output );
		$this->assertMatchesRegularExpression( '!/skins/\w+/js/main.js!', $output );
	}

	public function testGivenSandboxedWebContent_itIsReturnedAndContextPassed(): void {
		$factory = $this->getFactory();
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'template_with_content.html.twig' => '<p>{$ web_content("lorem", { "state": "fine" }) $}</p>',
			'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
			'lorem.twig' => 'More Dragons!'
		] );
		$provider = $this->getMockContentProvider();
		$provider->method( 'getWeb' )
			->with( 'lorem', [ 'state' => 'fine' ] )
			->willReturn( 'ipsum. all is <strong>fine</strong>.' );
		$factory->setContentProvider( $provider );

		$output = $factory->getLayoutTemplate( 'template_with_content.html.twig' )->render( [] );

		$this->assertSame( '<p>ipsum. all is <strong>fine</strong>.</p>', $output );
	}

	public function testSandboxedWebContentDoesntExist_exceptionIsThrown(): void {
		$factory = $this->getFactory();
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'template_with_content.html.twig' => '{$ web_content("lorem") $}',
		] );
		$provider = $this->getMockContentProvider();
		$provider->method( 'getWeb' )
			->with( 'lorem' )
			->willThrowException( new ContentException( "An exception occurred rendering 'lorem'" ) );
		$factory->setContentProvider( $provider );

		$this->expectException( RuntimeError::class );
		$this->expectExceptionMessageMatches( '/An exception occurred rendering \'lorem\'/' );

		$factory->getLayoutTemplate( 'template_with_content.html.twig' )->render( [] );
	}

	/**
	 * @return ContentProvider&MockObject
	 */
	public function getMockContentProvider(): ContentProvider {
		return $this->createMock( ContentProvider::class );
	}
}
