<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use FileFetcher\FileFetchingException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\LoggingCampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\MinBucketCountRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\DefaultBucketRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\StartAndEndTimeRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\UniqueBucketRule;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingCampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Validation\LoggingCampaignConfigurationLoader
 */
class LoggingCampaignConfigurationLoaderTest extends \PHPUnit\Framework\TestCase {

	/** @dataProvider throwablesDataProvider */
	public function testWhenCampaignConfigurationLoaderThrowsThrowable_exceptionIsProperlyHandled( \Throwable $throwable, string $expectedMessage ): void {
		$errorLogger = new ValidationErrorLogger();
		$loader = $this->newThrowingLoader( $throwable, $errorLogger );
		$loader->loadCampaignConfiguration( '' );
		$this->assertEquals( [ $expectedMessage ], $errorLogger->getErrors() );
	}

	public function throwablesDataProvider(): array {
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

	private function newThrowingLoader( \Throwable $e, ValidationErrorLogger $errorLogger ): LoggingCampaignConfigurationLoader {
		return new LoggingCampaignConfigurationLoader(
			new ThrowingCampaignConfigurationLoader( $e ),
			$errorLogger
		);
	}
}
