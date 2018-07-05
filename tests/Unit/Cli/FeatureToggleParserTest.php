<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use WMDE\Fundraising\Frontend\BucketTesting\Validation\FeatureToggleParser;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Validation\FeatureToggleParser
 */
class FeatureToggleParserTest extends \PHPUnit\Framework\TestCase {

	const CHOICE_FACTORY_LOCATION = 'tests/Fixtures/FakeChoiceFactory/FakeInconsistentChoiceFactory.php';

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
