<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use FileFetcher\InMemoryFileFetcher;
use FileFetcher\StubFileFetcher;
use PHPUnit\Framework\TestCase;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\Infrastructure\CampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\CampaignConfigurationLoader
 */
class CampaignConfigurationLoaderTest extends TestCase {

	const VALID_CONFIGURATION = <<<CFG
campaign1:
  url_key: c1
  start: "2015-01-01"
  end: "2015-02-02"
  active: true
  groups: [ foo, bar ]
  default_group: foo
CFG;

	const OVERRIDE_CONFIGURATION = <<<CFG
campaign1:
  active: false
  default_group: bar
CFG;

	public function testGivenOneConfigurationFile_itIsLoaded() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( self::VALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher );

		$this->assertArrayHasKey( 'campaign1', $loader->loadCampaignConfiguration( 'campaigns.yml' ) );
	}

	public function testGivenSeveralConfigurationFiles_theyAreLoaded() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new InMemoryFileFetcher( [
			'campaigns.yml' => self::VALID_CONFIGURATION,
			'override.yml' => self::OVERRIDE_CONFIGURATION
		] );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher );

		$config = $loader->loadCampaignConfiguration( 'campaigns.yml', 'override.yml' );
		$this->assertArrayHasKey( 'campaign1', $config );
		$this->assertFalse( $config['campaign1']['active'], 'Second configuration file should override first one' );
	}

	public function testGivenNonexistentFiles_exceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( false );
		$fileFetcher = new StubFileFetcher( self::VALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher );

		$this->expectExceptionMessageRegExp( '/No campaign configuration files found/' );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

	public function testGivenInvalidYaml_parseExceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( ' """ ' );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher );

		$this->expectException( ParseException::class );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

	public function testGivenInvalidFileStructure_configurationExceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( 'campaign: true' );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher );

		$this->expectException( InvalidConfigurationException::class );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

}
