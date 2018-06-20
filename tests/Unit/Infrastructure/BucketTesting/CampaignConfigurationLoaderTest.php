<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use FileFetcher\InMemoryFileFetcher;
use FileFetcher\StubFileFetcher;
use FileFetcher\ThrowingFileFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\CampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\BucketTesting\CampaignConfigurationLoader
 */
class CampaignConfigurationLoaderTest extends TestCase {

	const VALID_CONFIGURATION = <<<CFG
campaign1:
  url_key: c1
  start: "2015-01-01"
  end: "2015-02-02"
  active: true
  buckets: [ foo, bar ]
  default_bucket: foo
CFG;

	const OVERRIDE_CONFIGURATION = <<<CFG
campaign1:
  active: false
  default_bucket: bar
CFG;

	public function testGivenOneConfigurationFile_itIsLoaded() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( self::VALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, new VoidCache() );

		$this->assertArrayHasKey( 'campaign1', $loader->loadCampaignConfiguration( 'campaigns.yml' ) );
	}

	public function testGivenSeveralConfigurationFiles_theyAreLoaded() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new InMemoryFileFetcher(
			[
				'campaigns.yml' => self::VALID_CONFIGURATION,
				'override.yml' => self::OVERRIDE_CONFIGURATION
			]
		);
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, new VoidCache() );

		$config = $loader->loadCampaignConfiguration( 'campaigns.yml', 'override.yml' );
		$this->assertArrayHasKey( 'campaign1', $config );
		$this->assertFalse( $config['campaign1']['active'], 'Second configuration file should override first one' );
	}

	public function testGivenNonexistentFiles_exceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( false );
		$fileFetcher = new StubFileFetcher( self::VALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, new VoidCache() );

		$this->expectExceptionMessageRegExp( '/No campaign configuration files found/' );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

	public function testGivenInvalidYaml_parseExceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( ' """ ' );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, new VoidCache() );

		$this->expectException( ParseException::class );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

	public function testGivenInvalidFileStructure_configurationExceptionIsThrown() {
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( true );
		$fileFetcher = new StubFileFetcher( 'campaign: true' );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, new VoidCache() );

		$this->expectException( InvalidConfigurationException::class );
		$loader->loadCampaignConfiguration( 'campaigns.yml' );
	}

	public function testCategoriesAreAlreadyCached_nothingIsProcessed() {
		$campaignConfig = [
			'campaign1' => [
				'start' => '2018-10-01',
				'end' => '2018-12-31',
				'active' => false,
				'url_key' => 'c1',
				'buckets' => [ 'a', 'b' ],
				'default_bucket' => 'a'
			]
		];
		$filesystem = $this->createMock( Filesystem::class );
		$filesystem->method( 'exists' )->willReturn( false );
		$fileFetcher = new ThrowingFileFetcher();
		$cache = new ArrayCache();
		$cacheKey = md5( 'campaigns.yml' ); // TODO find better way to hide this implementation detail
		$cache->save( $cacheKey, $campaignConfig );
		$loader = new CampaignConfigurationLoader( $filesystem, $fileFetcher, $cache );

		$this->assertEquals( $campaignConfig, $loader->loadCampaignConfiguration( 'campaigns.yml' ) );
	}

}
