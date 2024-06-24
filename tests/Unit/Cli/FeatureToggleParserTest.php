<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\FeatureToggleParser;

#[CoversClass( FeatureToggleParser::class )]
class FeatureToggleParserTest extends TestCase {

	private const CHOICE_FACTORY_LOCATION = 'tests/Fixtures/FakeChoiceFactory/FakeInconsistentChoiceFactory.php';

	public function testWhenChoiceFactoryIsParsed_correctFeatureToggleChecksAreReturned(): void {
		$this->assertSame(
			[
				'campaigns.test_campaign.test_bucket_a',
				'campaigns.test_campaign.test_bucket_b',
				'campaigns.another_test_campaign.test_bucket_c',
				'campaigns.another_test_campaign.test_bucket_d',
				'campaigns.another_test_campaign.test_bucket_e',
				'campaigns.another_test_campaign.test_bucket_c',
				'campaigns.another_test_campaign.test_bucket_d',
			],
			FeatureToggleParser::getFeatureToggleChecks( self::CHOICE_FACTORY_LOCATION )
		);
	}
}
