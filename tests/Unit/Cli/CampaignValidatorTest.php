<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\MinBucketCountRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\DefaultBucketRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\StartAndEndTimeRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\UniqueBucketRule;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignValidator
 */
class CampaignValidatorTest extends \PHPUnit\Framework\TestCase {

	public function testWhenValidCampaignIsSupplied_validationPasses(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaign = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign, 'test_1', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign, 'test_2', Bucket::NON_DEFAULT );

		$campaignCollection = new CampaignCollection( $campaign );
		$validator = new CampaignValidator( $campaignCollection, $errorLogger );
		$this->assertTrue( $validator->isPassing() );
	}

	public function testWhenNoDefaultBucketIsSupplied_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaign = CampaignFixture::createCampaign();

		$rule = new DefaultBucketRule();
		$rule->validate( $campaign, $errorLogger );
		$this->assertEquals( [ 'test_campaign: Must have a valid default bucket.' ], $errorLogger->getErrors() );
	}

	public function testWhenCampaignEndTimeIsLowerThanOrEqualToStartTime_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaign = new Campaign(
			'test_campaign',
			'test_campaign',
			new CampaignDate( '2099-01-01' ),
			new CampaignDate( '2099-01-01' ),
			Campaign::ACTIVE
		);
		$rule = new StartAndEndTimeRule();
		$rule->validate( $campaign, $errorLogger );
		$this->assertEquals( [ 'test_campaign: Start date must be before end date.' ], $errorLogger->getErrors() );
	}

	public function testWhenCampaignBucketsAreNotUnique_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaign = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign );
		CampaignFixture::createBucket( $campaign );

		$rule = new UniqueBucketRule();
		$rule->validate( $campaign, $errorLogger );
		$this->assertEquals( [ 'test_campaign: Duplicate bucket test' ], $errorLogger->getErrors() );
	}

	public function testWhenCampaignHasLessThanTwoBuckets_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaign = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign );

		$rule = new MinBucketCountRule();
		$rule->validate( $campaign, $errorLogger );
		$this->assertEquals( [ 'test_campaign: Campaigns must have at least two buckets' ], $errorLogger->getErrors() );
	}
}
