<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use WMDE\Fundraising\Frontend\Infrastructure\CampaignConfiguration;

class CampaignConfigurationTest extends TestCase {

	use ConfigurationTestCaseTrait;

	protected function getConfiguration(): ConfigurationInterface {
		return new CampaignConfiguration();
	}

	public function testGivenValidConfigurationEntries_ValidationPasses() {
		$this->assertConfigurationIsValid( [ [
				'minimal_campaign' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2',
					'url_key' => 'mc'
				],
				'full_campaign' => [
					'description' => 'This is just a test of a campaign configuration with all possible values set',
					'reference' => 'https://example.com/',
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2',
					'url_key' => 'fc'
				]
		] ] );
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
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2'
				]
			],
			'start'
		];
		yield [
			[
				'missing_end' => [
					'start' => '2018-12-31',
					'active' => true,
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2'
				]
			],
			'end'
		];
		yield [
			[
				'missing_active' => [
					'start' => '2011-12-31',
					'end' => '2018-12-31',
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2'
				]
			],
			'active'
		];
		yield [
			[
				'missing_groups' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'default_group' => 'group2'
				]
			],
			'groups'
		];
		yield [
			[
				'missing_default_group' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'groups' => [ 'group1', 'group2' ],
				]
			],
			'default_group'
		];
		yield [
			[
				'missing_url_key' => [
					'start' => '2018-10-31',
					'end' => '2018-12-31',
					'active' => true,
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2'
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
						'groups' => [ 'group1', 'group2' ],
						'default_group' => 'group2',
						'url_key' => 'fc'
					],
					'second_campaign' => [
						'start' => '2019-01-01',
						'end' => '2019-12-31',
						'active' => false,
						'groups' => [ 'group1', 'group2' ],
						'default_group' => 'group2',
						'url_key' => 'sc'
					],
				],
				[
					'first_campaign' => [
						'start' => '2018-10-31',
						'end' => '2018-12-31',
						'active' => false,
						'groups' => [ 'group3' ],
						'default_group' => 'group3',
						'url_key' => 'fc'
					],
					'third_campaign' => [
						'start' => '2020-10-31',
						'end' => '2020-12-31',
						'active' => false,
						'groups' => [ 'default', 'fancy' ],
						'default_group' => 'default',
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
					'groups' => [ 'group1', 'group2', 'group3' ],
					'default_group' => 'group3',
					'url_key' => 'fc'
				],
				'second_campaign' => [
					'start' => '2019-01-01',
					'end' => '2019-12-31',
					'active' => false,
					'groups' => [ 'group1', 'group2' ],
					'default_group' => 'group2',
					'url_key' => 'sc'
				],
				'third_campaign' => [
					'start' => '2020-10-31',
					'end' => '2020-12-31',
					'active' => true,
					'groups' => [ 'default', 'fancy' ],
					'default_group' => 'default',
					'url_key' => 'tc'
				]
			]
			);
	}

}
