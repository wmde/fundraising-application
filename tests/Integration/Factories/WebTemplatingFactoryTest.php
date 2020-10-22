<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Error\RuntimeError;
use WMDE\Fundraising\ContentProvider\ContentException;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * Tests if the FunFunFactory correctly builds Twig environments for HTMl and Mail
 *
 * @covers \WMDE\Fundraising\Frontend\Factories\WebTemplatingFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\TwigFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\FunFunFactory::getSkinTwig
 *
 * @license GPL-2.0-or-later
 */
class WebTemplatingFactoryTest extends TestCase {

	private const TEMPLATE_DIR = 'templates';

	private function getFactory( array $configOverrides = [] ): FunFunFactory {
		$factory = TestEnvironment::newInstance( $configOverrides )->getFactory();
		$factory->setSkinDirectory( vfsStream::url( self::TEMPLATE_DIR ) );
		$factory->setSelectedBuckets( [ CampaignFixture::createBucket() ] );
		return $factory;
	}

	public function testFunFunFactoryPassesGlobalsFromConfig(): void {
		$factory = $this->getFactory( [
			'web-basepath' => '/mydevsubdir',
			'assets-path' => '/assets'
		] );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css"><img src="{$ assets_path $}/kitty.jpg" />'
		] );

		$output = $factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] );

		$this->assertSame( '<style src="/mydevsubdir/someFile.css"><img src="/assets/kitty.jpg" />', $output );
	}

	public function testFilePrefixerFilter(): void {
		$factory = $this->getFactory();
		$prefixer = new FilePrefixer( 'mylittleprefix' );
		$factory->setFilePrefixer( $prefixer );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'unicorns.html.twig' => '{$ "testfile.js"|prefix_file $}'
		] );

		$output = $factory->getLayoutTemplate( 'unicorns.html.twig' )->render( [] );

		$this->assertStringContainsString( 'mylittleprefix.testfile.js', $output );
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
	 * @return ContentProvider & MockObject
	 */
	public function getMockContentProvider(): ContentProvider {
		return $this->createMock( ContentProvider::class );
	}
}
