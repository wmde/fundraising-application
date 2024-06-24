<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration;

#[CoversClass( CampaignConfiguration::class )]
class CampaignConfigurationTest extends TestCase {

	use ConfigurationTestCaseTrait;

	public function testGivenValidConfigurationEntries_ValidationPasses(): void {
		$this->assertConfigurationIsValid(
			[
				'bucket_tests' => [
					'campaigns' => [
						'minimal_campaign' => [
							'start' => '2018-10-31',
							'end' => '2018-12-31',
							'active' => true,
							'buckets' => [ 'bucket1', 'bucket2' ],
							'default_bucket' => 'bucket2',
							'url_key' => 'mc',
							'param_only' => true
						],
						'full_campaign' => [
							'description' => 'This is just a test of a campaign configuration with all possible values set',
							'reference' => 'https://example.com/',
							'start' => '2018-10-31',
							'end' => '2018-12-31',
							'active' => true,
							'buckets' => [ 'bucket1', 'bucket2' ],
							'default_bucket' => 'bucket2',
							'url_key' => 'fc'
						]
					]
				]
			]
		);
	}

	/**
	 *
	 * @param array<string, mixed> $invalidConfig
	 * @param string $expectedReason
	 */
	#[DataProvider( 'invalidConfigurationProvider' )]
	public function testGivenMissingConfigurationEntries_ValidationFails( array $invalidConfig, string $expectedReason ): void {
		$this->assertConfigurationIsInvalid(
			[
				'bucket_tests' => [
					'campaigns' => $invalidConfig
				]
			],
			$expectedReason
		);
	}

	/**
	 * @return iterable<array{0: array<string, mixed>, 1: string}>
	 */
	public static function invalidConfigurationProvider(): iterable {
		yield [
			[
				'missing_start' => [
					'end' => '2018-12-31',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket2'
				]
			],
			'bucket_tests.campaigns.missing_start'
		];
		yield [
			[
				'missing_end' => [
					'start' => '2018-12-31',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket2'
				]
			],
			'bucket_tests.campaigns.missing_end'
		];
		yield [
			[
				'missing_active' => [
					'start' => '2011-12-31',
					'end' => '2018-12-31',
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket2'
				]
			],
			'bucket_tests.campaigns.missing_active'
		];
		yield [
			[
				'missing_buckets' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'default_bucket' => 'bucket2'
				]
			],
			'bucket_tests.campaigns.missing_buckets'
		];
		yield [
			[
				'missing_default_bucket' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
				]
			],
			'bucket_tests.campaigns.missing_default_bucket'
		];
		yield [
			[
				'missing_url_key' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket2'
				]
			],
			'bucket_tests.campaigns.missing_url_key'
		];
	}

	public function testGivenMultipleConfigurationEntries_TheyAreMergedAndOverwritten(): void {
		$this->assertProcessedConfigurationEquals(
			[
				// Since the configurations are merged at the root node, we omit the 'bucket_testing' root node
				[
					'campaigns' => [
						'first_campaign' => [
							'start' => '2018-10-31',
							'end' => '2018-12-31',
							'active' => true,
							'buckets' => [ 'bucket1', 'bucket2' ],
							'default_bucket' => 'bucket2',
							'url_key' => 'fc'
						],
						'second_campaign' => [
							'start' => '2019-01-01',
							'end' => '2019-12-31',
							'active' => false,
							'buckets' => [ 'bucket1', 'bucket2' ],
							'default_bucket' => 'bucket2',
							'url_key' => 'sc'
						],
					],
				],
				[
					'campaigns' => [
						'first_campaign' => [
							'start' => '2018-10-31',
							'end' => '2018-12-31',
							'active' => false,
							'buckets' => [ 'bucket3' ],
							'default_bucket' => 'bucket3',
							'url_key' => 'fc',
							'param_only' => true
						],
						'third_campaign' => [
							'start' => '2020-10-31',
							'end' => '2020-12-31',
							'active' => false,
							'buckets' => [ 'default', 'fancy' ],
							'default_bucket' => 'default',
							'url_key' => 'tc'
						]
					],
				],
				// partial overrides are allowed
				[
					'campaigns' => [
						'third_campaign' => [
							'active' => true
						]
					]
				],
			],
			[
				'campaigns' => [
					'first_campaign' => [
						'start' => '2018-10-31',
						'end' => '2018-12-31',
						'active' => false,
						'buckets' => [ 'bucket1', 'bucket2', 'bucket3' ],
						'default_bucket' => 'bucket3',
						'url_key' => 'fc',
						'param_only' => true
					],
					'second_campaign' => [
						'start' => '2019-01-01',
						'end' => '2019-12-31',
						'active' => false,
						'buckets' => [ 'bucket1', 'bucket2' ],
						'default_bucket' => 'bucket2',
						'url_key' => 'sc',
						'param_only' => false
					],
					'third_campaign' => [
						'start' => '2020-10-31',
						'end' => '2020-12-31',
						'active' => true,
						'buckets' => [ 'default', 'fancy' ],
						'default_bucket' => 'default',
						'url_key' => 'tc',
						'param_only' => false
					]
				]
			]
		);
	}

	protected function getConfiguration(): ConfigurationInterface {
		return new CampaignConfiguration();
	}

}
