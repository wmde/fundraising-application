<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use RemotelyLiving\Doorkeeper\Features\Feature;
use RemotelyLiving\Doorkeeper\Rules\StringHash;
use RemotelyLiving\Doorkeeper\Rules\TimeAfter;
use RemotelyLiving\Doorkeeper\Rules\TimeBefore;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignFeatureBuilder;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\CampaignFeatureBuilder
 */
class CampaignFeatureBuilderTest extends TestCase {

	public function testWhenNoCampaignsAreDefined_featureSetIsEmpty() {
		$factory = new CampaignFeatureBuilder();
		$result = $factory->getFeatures()->jsonSerialize();
		$this->assertSame( [ 'features' => null ], $result );
	}

	public function testCampaignAndBucketNamesAreConvertedIntoFeatureNames() {
		$factory = new CampaignFeatureBuilder( $this->newInactiveCampaign(), $this->newActiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->offsetExists( 'campaigns.test_inactive.bucket1' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_inactive.bucket2' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_active.bucket1' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_active.bucket2' ) );
	}

	private function newInactiveCampaign(): Campaign {
		$campaign = new Campaign(
			'test_inactive',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::INACTIVE
		);
		$campaign->addBucket( new Bucket( 'bucket1', $campaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );
		return $campaign;
	}

	private function newActiveCampaign(): Campaign {
		$start = new \DateTime( '2000-01-01' );
		$end = new \DateTime( '2099-01-01' );
		$campaign = new Campaign(
			'test_active',
			't1',
			$start,
			$end,
			Campaign::ACTIVE
		);
		$campaign->addBucket( new Bucket( 'bucket1', $campaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $campaign, Bucket::NON_DEFAULT ) );
		return $campaign;
	}

	public function testWhenCampaignIsInactive_AllFeaturesExceptTheDefaultAreInactive() {
		$factory = new CampaignFeatureBuilder( $this->newInactiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_inactive.bucket1' )->isEnabled() );
		$this->assertFalse( $features->getFeatureByName( 'campaigns.test_inactive.bucket2' )->isEnabled() );
	}

	public function testWhenCampaignIsActive_AllFeaturesAreEnabled() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_active.bucket1' )->isEnabled() );
		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_active.bucket2' )->isEnabled() );
	}

	public function testWhenCampaignWithTwoBucketsIsActive_AllHaveStringHashRulesBasedOnBucketName() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertStringHashRule( $features->getFeatureByName( 'campaigns.test_active.bucket1' ), 'test_active.bucket1' );
		$this->assertStringHashRule( $features->getFeatureByName( 'campaigns.test_active.bucket2' ), 'test_active.bucket2' );
	}

	private function assertStringHashRule( Feature $feature, string $expectedStringHashValue ) {
		$rules = $feature->getRules();
		$this->assertCount( 1, $rules, 'Feature should have only one rule' );
		$this->assertInstanceOf( StringHash::class, $rules[0], 'Rule must be StringHash' );
		$this->assertEquals( $expectedStringHashValue, $rules[0]->getValue() );
	}

	public function testWhenCampaignIsActive_AllFeaturesExceptTheDefaultHaveDatePreconditions() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();
		$rules = $features->getFeatureByName( 'campaigns.test_active.bucket2' )->getRules();

		$timeAfter = $rules[0]->getPrerequisites()[0]->getValue();
		$timeBefore = $rules[0]->getPrerequisites()[0]->getPrerequisites()[0]->getValue();
		$this->assertEquals( '2000-01-01 00:00:00', $timeAfter );
		$this->assertEquals( '2099-01-01 00:00:00', $timeBefore );
		$this->assertInstanceOf( TimeAfter::class, $rules[0]->getPrerequisites()[0] );
		$this->assertInstanceOf( TimeBefore::class, $rules[0]->getPrerequisites()[0]->getPrerequisites()[0] );
	}

}
