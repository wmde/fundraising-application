<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation;

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
 * @covers \WMDE\Fundraising\Frontend\Factories\TwigFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\FunFunFactory::getSkinTwig
 * @covers \WMDE\Fundraising\Frontend\Factories\FunFunFactory::getMailerTwig
 *
 * @license GPL-2.0-or-later
 */
class TwigEnvironmentTest extends TestCase {

	private const TEMPLATE_DIR = 'templates';

	public function testTwigInstanceUsesDollarPlaceholdersForVariables(): void {
		$factory = $this->getFactory();
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'variableReplacement.twig' => '{$ testvar $}'
		] );

		$output = $factory->getLayoutTemplate( 'variableReplacement.twig' )->render( [ 'testvar' => 'Meeow!' ] );

		$this->assertSame( 'Meeow!', $output );
	}

	private function getFactory( array $configOverrides = [] ): FunFunFactory {
		$factory = TestEnvironment::newInstance( $configOverrides )->getFactory();
		$factory->setSkinDirectory( vfsStream::url( self::TEMPLATE_DIR ) );
		$factory->setSelectedBuckets( [ CampaignFixture::createBucket() ] );
		return $factory;
	}

	public function testWhenWebBasePathIsEmpty_templatedPathsReferToRootPath(): void {
		$factory = $this->getFactory( [ 'web-basepath' => '' ] );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css">'
		] );

		$output = $factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] );

		$this->assertSame( '<style src="/someFile.css">', $output );
	}

	public function testWhenWebBasePathIsNotEmpty_templatedPathsReferToRootPath(): void {
		$factory = $this->getFactory( [
			'web-basepath' => '/mydevsubdir'
		] );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'kittens.html.twig' => '<style src="{$ basepath $}/someFile.css">'
		] );

		$output = $factory->getLayoutTemplate( 'kittens.html.twig' )->render( [] );

		$this->assertSame( '<style src="/mydevsubdir/someFile.css">', $output );
	}

	public function testFilePrefixerIsCalledInTemplate(): void {
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
			'template_with_content.twig' => '<p>{$ web_content("lorem", { "state": "fine" }) $}</p>',
			'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
			'lorem.twig' => 'More Dragons!'
		] );
		$provider = $this->getMockContentProvider();
		$provider->method( 'getWeb' )
			->with( 'lorem', [ 'state' => 'fine' ] )
			->willReturn( 'ipsum. all is <strong>fine</strong>.' );
		$factory->setContentProvider( $provider );

		$output = $factory->getLayoutTemplate( 'template_with_content.twig' )->render( [] );

		$this->assertSame( '<p>ipsum. all is <strong>fine</strong>.</p>', $output );
	}

	public function testGivenSandboxedMailContent_itIsReturnedAndContextPassed(): void {
		$mailTemplateDir = self::TEMPLATE_DIR . '_mail';
		$factory = $this->getFactory( [
			'mailer-twig' => [ 'loaders' => [ 'filesystem' => [ 'template-dir' => vfsStream::url( $mailTemplateDir ) ] ] ]
		] );
		vfsStream::setup( $mailTemplateDir, null, [
			'template_with_content.twig' => '{$ mail_content("something", { "donation_id": 45 }) $} end',
			'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
			'lorem.twig' => 'More Dragons!'
		] );
		$provider = $this->getMockContentProvider();
		$provider->method( 'getMail' )
			->with( 'something', [ 'donation_id' => 45 ] )
			->willReturn( 'you got mail' );
		$factory->setContentProvider( $provider );

		$output = $factory->getMailerTemplate( 'template_with_content.twig' )->render( [] );

		$this->assertSame( 'you got mail end', $output );
	}

	public function testSandboxedWebContentDoesntExist_exceptionIsThrown(): void {
		$factory = $this->getFactory();
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'template_with_content.twig' => '{$ web_content("lorem") $}',
		] );
		$provider = $this->getMockContentProvider();
		$provider->method( 'getWeb' )
			->with( 'lorem' )
			->willThrowException( new ContentException( "An exception occured rendering 'lorem'" ) );
		$factory->setContentProvider( $provider );

		$this->expectException( RuntimeError::class );
		$this->expectExceptionMessageMatches( '/An exception occured rendering \'lorem\'/' );

		$factory->getLayoutTemplate( 'template_with_content.twig' )->render( [] );
	}

	/**
	 * @return ContentProvider & MockObject
	 */
	public function getMockContentProvider(): ContentProvider {
		return $this->createMock( ContentProvider::class );
	}
}
