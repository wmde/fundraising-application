<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Infrastructure;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use WMDE\Fundraising\Frontend\Factories\TranslationFactory;
use WMDE\Fundraising\Frontend\Infrastructure\DirectoryLoader;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\DirectoryLoader
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DirectoryLoaderTest extends \PHPUnit\Framework\TestCase {

	/* @var vfsStreamDirectory */
	private $baseDir;

	public function setUp() {
		$this->baseDir = vfsStream::setup( 'i18n' );

		$germanStuff = vfsStream::newDirectory( 'de_DE' )->at( $this->baseDir );
		$germanSubDir = vfsStream::newDirectory( 'pages' )->at( $germanStuff );
		vfsStream::newFile( 'translated_template.html.twig' )->at( $germanSubDir )->withContent( 'Deutscher Text' );

		$englishStuff = vfsStream::newDirectory( 'en_US' )->at( $this->baseDir );
		$englishSubDir = vfsStream::newDirectory( 'pages' )->at( $englishStuff );
		vfsStream::newFile( 'translated_template.html.twig' )->at( $englishSubDir )->withContent( 'English text' );
	}

	/** @dataProvider translationProvider */
	public function testGivenDirectoryLoader_translatorReturnsMessage( $locale, $expectedTranslation ) {
		$translator = ( new TranslationFactory() )->create( [ 'directory' => new DirectoryLoader() ] );
		$translator->addResource( 'directory', $this->baseDir->url() . '/en_US', 'en_US', 'pages' );
		$translator->addResource( 'directory', $this->baseDir->url() . '/de_DE', 'de_DE', 'pages' );

		$this->assertSame( $expectedTranslation, $translator->trans( 'translated_template.html.twig', [], 'pages', $locale ) );
	}

	public function translationProvider() {
		return [
			[ 'en_US', 'English text' ],
			[ 'de_DE', 'Deutscher Text' ]
		];
	}

}
