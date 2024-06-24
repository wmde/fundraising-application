<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use FileFetcher\FileFetchingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\LoggingCampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingCampaignConfigurationLoader;

#[CoversClass( LoggingCampaignConfigurationLoader::class )]
class LoggingCampaignConfigurationLoaderTest extends TestCase {

	#[DataProvider( 'throwablesDataProvider' )]
	public function testWhenCampaignConfigurationLoaderThrowsThrowable_exceptionIsProperlyHandled( \Throwable $throwable, string $expectedMessage ): void {
		$errorLogger = new CampaignErrorCollection();
		$loader = $this->newThrowingLoader( $throwable, $errorLogger );
		$loader->loadCampaignConfiguration( '' );
		$this->assertEquals( [ $expectedMessage ], $errorLogger->getErrors() );
	}

	public static function throwablesDataProvider(): array {
		return [
			[
				new ParseException( 'Test Message' ),
				'Failed to parse campaign YAML: Test Message'
			],
			[
				new FileFetchingException( 'Test Message' ),
				'YAML configuration file read error: Could not fetch file: Test Message'
			],
			[
				new InvalidConfigurationException( 'Test Message' ),
				'Invalid campaign YAML configuration: Test Message'
			],
			[
				new \RuntimeException( 'Test Message' ),
				'Runtime error while parsing campaign YAML: Test Message'
			],
			[
				new \Exception( 'Test Message' ),
				'Exception: Test Message'
			],
		];
	}

	private function newThrowingLoader( \Throwable $e, CampaignErrorCollection $errorLogger ): LoggingCampaignConfigurationLoader {
		return new LoggingCampaignConfigurationLoader(
			new ThrowingCampaignConfigurationLoader( $e ),
			$errorLogger
		);
	}
}
