<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalAPIURLGeneratorConfigLoader;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalURLGeneratorConfigReader;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\PayPalAPIURLGeneratorConfigLoader
 */
class PayPalAPIURLGeneratorConfigLoaderTest extends TestCase {
	private const TEST_CONFIG_FILE = __DIR__ . '/../../Data/files/paypal_api.yml';
	private const CACHE_KEY = "3a0212de40bd87d9c89b2e515af46836";
	private vfsStreamDirectory $filesystem;

	/**
	 * @before
	 */
	public function filesystemSetUp(): void {
		$this->filesystem = vfsStream::setup();
	}

	public function testWritesCacheIfEmpty(): void {
		$configFile = $this->givenConfigFile();
		$configCache = new Psr16Cache( new ArrayAdapter() );
		$loader = new PayPalAPIURLGeneratorConfigLoader( $configCache );

		$loader->load(
			$configFile->url(),
			'donation',
			'de_DE'
		);

		$this->assertTrue( $configCache->has( self::CACHE_KEY ) );
		$this->assertSame( PayPalURLGeneratorConfigReader::readConfig( self::TEST_CONFIG_FILE ), $configCache->get( self::CACHE_KEY ) );
	}

	#[DoesNotPerformAssertions]
	public function testGivenCachedConfigItReturnsFromCache(): void {
		$configFile = $this->givenConfigFile();
		// set content to empty to make the loader fail on purpose when it tries to read the file (because here it should never do that)
		$configFile->setContent( "" );

		$filledConfigCache = new Psr16Cache( new ArrayAdapter() );
		$filledConfigCache->set( self::CACHE_KEY, PayPalURLGeneratorConfigReader::readConfig( self::TEST_CONFIG_FILE ) );
		$loader = new PayPalAPIURLGeneratorConfigLoader( $filledConfigCache );

		try {
			$loader->load(
				$configFile->url(),
				'donation',
				'de_DE'
			);
		} catch ( \Exception ) {
			$this->fail( 'The loader should not throw an exception due to empty configuration file, because the file should never be read' );
		}
	}

	public function testUsesSpecificSectionOfTheConfigFile(): void {
		$loader = new PayPalAPIURLGeneratorConfigLoader( new Psr16Cache( new NullAdapter() ) );
		$result = $loader->load(
			self::TEST_CONFIG_FILE,
			'membership',
			'en_GB'
		);

		$this->assertSame( "Membership", $result->productName );
	}

	private function givenConfigFile(): vfsStreamFile {
		return vfsStream::newFile( 'paypal_api.yml' )
			->at( $this->filesystem )
			->setContent( file_get_contents( self::TEST_CONFIG_FILE ) )
			->lastModified( 1611619200 );
	}
}
