<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator
 */
class CampaignUtilizationValidatorTest extends \PHPUnit\Framework\TestCase {

	public function testWhenCampaignConfigurationMatchesChoiceFactory_validationPasses(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaignCollection = $this->newTestCampaignCollection();

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			'tests/Fixtures/ChoiceFactoryMock.php',
			$errorLogger
		);
		$this->assertTrue( $validator->isPassing() );
	}

	public function testWhenCampaignConfigurationHasUnimplementedBuckets_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();
		$campaignCollection = $this->newTestCampaignCollection();

		CampaignFixture::createBucket(
			$campaignCollection->getIterator()->offsetGet( 1 ),
			'test_bucket_f',
			Bucket::NON_DEFAULT
		);

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			'tests/Fixtures/ChoiceFactoryMock.php',
			$errorLogger
		);
		$this->assertSame(
			[ 'Bucket campaigns.another_test_campaign.test_bucket_f is configured but no implementation can be found in ChoiceFactory.' ],
			$validator->getErrors()
		);
	}

	public function testWhenCampaignConfigurationHasNoBuckets_validationFails(): void {
		$errorLogger = new ValidationErrorLogger();

		$campaign01 = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign01, 'test_bucket_a', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign01, 'test_bucket_b', Bucket::NON_DEFAULT );

		$campaign02 = CampaignFixture::createCampaign( 'another_test_campaign' );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_c', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_d', Bucket::NON_DEFAULT );

		$campaignCollection = new CampaignCollection( $campaign01, $campaign02 );

		$validator = new CampaignUtilizationValidator(
			$campaignCollection,
			[ '' ],
			'tests/Fixtures/ChoiceFactoryMock.php',
			$errorLogger
		);
		$this->assertSame(
			[
				'Feature toggle check for campaigns.another_test_campaign.test_bucket_e is implemented but no campaign configuration can be found.'
			],
			$validator->getErrors()
		);
	}

	public function newTestCampaignCollection(): CampaignCollection {
		$campaign01 = CampaignFixture::createCampaign();
		CampaignFixture::createBucket( $campaign01, 'test_bucket_a', Bucket::DEFAULT );
		CampaignFixture::createBucket( $campaign01, 'test_bucket_b', Bucket::NON_DEFAULT );

		$campaign02 = CampaignFixture::createCampaign( 'another_test_campaign' );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_e', Bucket::NON_DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_d', Bucket::NON_DEFAULT );
		CampaignFixture::createBucket( $campaign02, 'test_bucket_c', Bucket::DEFAULT );

		return new CampaignCollection( $campaign01, $campaign02 );
	}
}
