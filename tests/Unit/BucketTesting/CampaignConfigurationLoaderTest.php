<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use FileFetcher\SimpleFileFetcher;
use FileFetcher\ThrowingFileFetcher;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\CampaignConfigurationLoader
 */
class CampaignConfigurationLoaderTest extends TestCase {

	private const VALID_CONFIGURATION = <<<CFG
campaigns:
    campaign1:
      url_key: c1
      start: "2015-01-01"
      end: "2015-02-02"
      active: true
      buckets: [ foo, bar ]
      default_bucket: foo
CFG;

	private const OVERRIDE_CONFIGURATION = <<<CFG
campaigns:
    campaign1:
      active: false
      default_bucket: bar
CFG;

	private const INVALID_CONFIGURATION = <<<CFG
campaigns:
    campaign1: "Campaign definition should be an object, this is wrong"
CFG;

	private vfsStreamDirectory $filesystem;

	/**
	 * @before
	 */
	public function filesystemSetUp(): void {
		$this->filesystem = vfsStream::setup();
	}

	public function testGivenOneConfigurationFile_itIsLoaded(): void {
		$campaignFile = vfsStream::newFile( 'campaigns.yml' )
			->at( $this->filesystem )
			->setContent( self::VALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), new Psr16Cache( new NullAdapter() ) );

		$this->assertArrayHasKey( 'campaign1', $loader->loadCampaignConfiguration( $campaignFile->url() ) );
	}

	public function testGivenSeveralConfigurationFiles_theyAreLoaded(): void {
		$campaignFile = vfsStream::newFile( 'campaigns.yml' )
			->at( $this->filesystem )
			->setContent( self::VALID_CONFIGURATION );
		$overrideFile = vfsStream::newFile( 'override.yml' )
			->at( $this->filesystem )
			->setContent( self::OVERRIDE_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), new Psr16Cache( new NullAdapter() ) );

		$config = $loader->loadCampaignConfiguration( $campaignFile->url(), $overrideFile->url() );
		$this->assertArrayHasKey( 'campaign1', $config );
		$this->assertFalse( $config['campaign1']['active'], 'Second configuration file should override first one' );
	}

	public function testGivenNonexistentFiles_exceptionIsThrown(): void {
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), new Psr16Cache( new NullAdapter() ) );

		$this->expectExceptionMessageMatches( '/No campaign configuration files found/' );
		$loader->loadCampaignConfiguration( vfsStream::url( 'campaigns.yml' ) );
	}

	public function testGivenInvalidYaml_parseExceptionIsThrown(): void {
		$campaignFile = vfsStream::newFile( 'campaigns.yml' )
			->at( $this->filesystem )
			->setContent( ' """ ' );
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), new Psr16Cache( new NullAdapter() ) );

		$this->expectException( ParseException::class );
		$loader->loadCampaignConfiguration( $campaignFile->url() );
	}

	public function testGivenInvalidFileStructure_configurationExceptionIsThrown(): void {
		$campaignFile = vfsStream::newFile( 'campaigns.yml' )
			->at( $this->filesystem )
			->setContent( self::INVALID_CONFIGURATION );
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), new Psr16Cache( new NullAdapter() ) );

		$this->expectException( InvalidConfigurationException::class );
		$loader->loadCampaignConfiguration( $campaignFile->url() );
	}

	public function testCategoriesAreAlreadyCached_nothingIsProcessed(): void {
		$campaignFile = vfsStream::newFile( 'campaigns.yml' )
			->at( $this->filesystem )
			->setContent( self::VALID_CONFIGURATION )
			->lastModified( 1611619200 );
		$campaignConfig = [
			'campaigns' => [
				'campaign1' => [
					'start' => '2018-10-01',
					'end' => '2018-12-31',
					'active' => false,
					'url_key' => 'c1',
					'buckets' => [ 'a', 'b' ],
					'default_bucket' => 'a'
				]
			]
		];
		$fileFetcher = new ThrowingFileFetcher();
		$cache = new Psr16Cache( new ArrayAdapter() );
		// @see CampaignConfigurationLoader::getCacheKey to see how this has was generated
		$cacheKey = '67984c5cde9f85dbd137b8832eff88b0';
		$cache->set( $cacheKey, $campaignConfig );
		$loader = new CampaignConfigurationLoader( $fileFetcher, $cache );

		$cachedCampaigns = $loader->loadCampaignConfiguration( $campaignFile->url() );
		$this->assertArrayHasKey( 'campaign1', $cachedCampaigns );
		$this->assertFalse( $cachedCampaigns['campaign1']['active'], 'We should get the value from the cached campaign' );
	}

	public function testWhenNoConfigurationFilesExist_cacheWillBeSkipped(): void {
		$forbiddenCache = $this->createMock( Psr16Cache::class );
		$forbiddenCache->method( 'get' )->willThrowException( new \LogicException( 'Cache access is not allowed' ) );
		$loader = new CampaignConfigurationLoader( new SimpleFileFetcher(), $forbiddenCache );

		$this->expectExceptionMessageMatches( '/No campaign configuration files found/' );
		$loader->loadCampaignConfiguration( vfsStream::url( 'campaigns.yml' ) );
	}
}
