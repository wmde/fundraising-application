<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator
 */
class CampaignUtilizationValidatorTest extends TestCase {

	public function testWhenCampaignConfigurationMatchesChoiceFactory_validationPasses(): void {
		$errorLogger = new CampaignErrorCollection();
		$campaignCollection = $this->newTestCampaignCollection();
		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			$this->newValidCampaignFeaturesArray(),
			$errorLogger
		);
		$this->assertTrue( $validator->isPassing() );
	}

	public function testWhenCampaignConfigurationHasUnimplementedBuckets_validationFails(): void {
		$errorLogger = new CampaignErrorCollection();
		$campaign02 = $this->createCampaign02();
		CampaignFixture::createBucket(
			$campaign02,
			'test_bucket_f',
			Bucket::NON_DEFAULT
		);
		$campaignCollection = new CampaignCollection( $this->createCampaign01(), $campaign02 );

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			$this->newValidCampaignFeaturesArray(),
			$errorLogger
		);
		$this->assertSame(
			[ 'Bucket campaigns.another_test_campaign.test_bucket_f is configured but no implementation can be found in ChoiceFactory.' ],
			$validator->getErrors()
		);
	}

	public function testWhenCampaignConfigurationIsMissingImplementedBuckets_validationFails(): void {
		$errorLogger = new CampaignErrorCollection();

		$campaign01 = $this->createCampaign01();

		$campaign02 = CampaignFixture::createCampaign( 'another_test_campaign' );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_c', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_d', Bucket::NON_DEFAULT );

		$campaignCollection = new CampaignCollection( $campaign01, $campaign02 );

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			$this->newValidCampaignFeaturesArray(),
			$errorLogger
		);
		$this->assertSame(
			[
				'Feature toggle check for campaigns.another_test_campaign.test_bucket_e is implemented but no campaign configuration can be found.'
			],
			$validator->getErrors()
		);
	}

	public function testWhenCampaignBucketsAreImplementedInconsistently_validationFails(): void {
		$errorLogger = new CampaignErrorCollection();
		$campaignCollection = $this->newTestCampaignCollection();

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			$this->newInconsistentCampaignFeaturesArray(),
			$errorLogger
		);
		$this->assertSame(
			[
				'Campaign buckets for "campaigns.another_test_campaign.test_bucket_e" have not been implemented consistently.'
			],
			$validator->getErrors()
		);
	}

	public function testWhenFeatureToggleAreMissingCampaignName_validationFails(): void {
		$errorLogger = new CampaignErrorCollection();
		$campaignCollection = $this->newTestCampaignCollection();

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			$this->newBrokenCampaignFeaturesArray(),
			$errorLogger
		);
		$this->assertContains(
			'Feature toggle name did not contain a campaign name. The campaign name should be the last ' .
			'item in a dot-separated string (with at least one "." character). Feature toggle name was: ' .
			'campaigns:test_campaign:test_bucket_a',
			$validator->getErrors()
		);
		$this->assertContains(
			'Feature toggle name did not contain a campaign name. The campaign name should be the last ' .
			'item in a dot-separated string (with at least one "." character). Feature toggle name was: ' .
			'campaigns:test_campaign:test_bucket_b',
			$validator->getErrors()
		);
	}

	public function newTestCampaignCollection(): CampaignCollection {
		$campaign01 = $this->createCampaign01();
		$campaign02 = $this->createCampaign02();
		return new CampaignCollection( $campaign01, $campaign02 );
	}

	private function createCampaign01(): Campaign {
		$campaign01 = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign01, 'test_bucket_a', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign01, 'test_bucket_b', Bucket::NON_DEFAULT );
		return $campaign01;
	}

	private function createCampaign02(): Campaign {
		$campaign02 = CampaignFixture::createCampaign( 'another_test_campaign' );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_e', Bucket::NON_DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_d', Bucket::NON_DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_c', Bucket::DEFAULT );
		return $campaign02;
	}

	public function newValidCampaignFeaturesArray(): array {
		return [
			'campaigns.test_campaign.test_bucket_a',
			'campaigns.test_campaign.test_bucket_b',
			'campaigns.another_test_campaign.test_bucket_c',
			'campaigns.another_test_campaign.test_bucket_d',
			'campaigns.another_test_campaign.test_bucket_e'
		];
	}

	public function newInconsistentCampaignFeaturesArray(): array {
		return [
			'campaigns.test_campaign.test_bucket_a',
			'campaigns.test_campaign.test_bucket_b',
			'campaigns.another_test_campaign.test_bucket_c',
			'campaigns.another_test_campaign.test_bucket_d',
			'campaigns.another_test_campaign.test_bucket_e',
			'campaigns.another_test_campaign.test_bucket_c',
			'campaigns.another_test_campaign.test_bucket_d',
		];
	}

	public function newBrokenCampaignFeaturesArray(): array {
		return [
			'campaigns:test_campaign:test_bucket_a',
			'campaigns:test_campaign:test_bucket_b',
			'campaigns.another_test_campaign.test_bucket_c',
			'campaigns.another_test_campaign.test_bucket_d',
			'campaigns.another_test_campaign.test_bucket_e',
			'campaigns.another_test_campaign.test_bucket_c',
			'campaigns.another_test_campaign.test_bucket_d',
		];
	}
}
