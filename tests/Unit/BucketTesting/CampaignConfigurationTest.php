<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration;

class CampaignConfigurationTest extends TestCase {

	use ConfigurationTestCaseTrait;

	public function testGivenValidConfigurationEntries_ValidationPasses() {
		$this->assertConfigurationIsValid(
			[
				[
					'minimal_campaign' => [
						'start' => '2018-10-31',
						'end' => '2018-12-31',
						'active' => true,
						'buckets' => [ 'bucket1', 'bucket2' ],
						'default_bucket' => 'bucket2',
						'url_key' => 'mc'
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
				] ]
		);
	}

	/**
	 * @dataProvider invalidConfigurationProvider
	 */
	public function testGivenMissingConfigurationEntries_ValidationFails( array $invalidConfig, string $expectedReason ) {
		$this->assertConfigurationIsInvalid( [ $invalidConfig ], $expectedReason );
	}

	public function invalidConfigurationProvider(): iterable {
		yield [
			[
				'missing_start' => [
					'end' => '2018-12-31',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket2'
				]
			],
			'start'
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
			'end'
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
			'active'
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
			'buckets'
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
			'default_bucket'
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
			'url_key'
		];
	}

	public function testGivenMultipleConfigurationEntries_TheyAreMergedAndOverwritten() {
		$this->assertProcessedConfigurationEquals(
			[
				[
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
				[
					'first_campaign' => [
						'start' => '2018-10-31',
						'end' => '2018-12-31',
						'active' => false,
						'buckets' => [ 'bucket3' ],
						'default_bucket' => 'bucket3',
						'url_key' => 'fc'
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
				// partial overrides are allowed
				[
					'third_campaign' => [
						'active' => true
					]
				]
			],
			[
				'first_campaign' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => false,
					'buckets' => [ 'bucket1', 'bucket2', 'bucket3' ],
					'default_bucket' => 'bucket3',
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
				'third_campaign' => [
					'start' => '2020-10-31',
					'end' => '2020-12-31',
					'active' => true,
					'buckets' => [ 'default', 'fancy' ],
					'default_bucket' => 'default',
					'url_key' => 'tc'
				]
			]
		);
	}

	protected function getConfiguration(): ConfigurationInterface {
		return new CampaignConfiguration();
	}

}
