<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use RemotelyLiving\Doorkeeper\Rules\Percentage;
use RemotelyLiving\Doorkeeper\Rules\TimeAfter;
use RemotelyLiving\Doorkeeper\Rules\TimeBefore;
use WMDE\Fundraising\Frontend\Infrastructure\Campaign;
use WMDE\Fundraising\Frontend\Infrastructure\CampaignFeatureBuilder;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\CampaignFeatureBuilder
 */
class CampaignFeatureBuilderTest extends TestCase {

	public function testWhenNoCampaignsAreDefined_featureSetIsEmpty() {
		$factory = new CampaignFeatureBuilder();
		$result = $factory->getFeatures()->jsonSerialize();
		$this->assertSame( [ 'features' => null ], $result );
	}

	public function testCampaignAndGroupNamesAreConvertedIntoFeatureNames() {
		$factory = new CampaignFeatureBuilder( $this->newInactiveCampaign(), $this->newActiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->offsetExists( 'campaigns.test_inactive.group1' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_inactive.group2' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_active.group1' ) );
		$this->assertTrue( $features->offsetExists( 'campaigns.test_active.group2' ) );
	}

	public function testWhenCampaignIsInactive_AllFeaturesExceptTheDefaultAreInactive() {
		$factory = new CampaignFeatureBuilder( $this->newInactiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_inactive.group1' )->isEnabled() );
		$this->assertFalse( $features->getFeatureByName( 'campaigns.test_inactive.group2' )->isEnabled() );
	}

	public function testWhenCampaignIsActive_AllFeaturesAreEnabled() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();

		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_active.group1' )->isEnabled() );
		$this->assertTrue( $features->getFeatureByName( 'campaigns.test_active.group2' )->isEnabled() );
	}

	public function testWhenCampaignIsActive_AllFeaturesExceptTheDefaultHaveDatePreconditions() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();
		$rules = $features->getFeatureByName( 'campaigns.test_active.group2' )->getRules();

		$this->assertCount( 0, $features->getFeatureByName( 'campaigns.test_active.group1' )->getRules(), 'Default group feature should have no rules' );

		$this->assertEquals( new TimeAfter( '2000-01-01' ), $rules[0] );
		$this->assertEquals( new TimeBefore( '2099-01-01' ), $rules[1] );
	}

	public function testWhenCampaignWithTwoGroupsIsActive_AllFeaturesExceptTheDefaultHavePercentagesBasedOnNumberOfGroups() {
		$factory = new CampaignFeatureBuilder( $this->newActiveCampaign() );

		$features = $factory->getFeatures();
		$rules = $features->getFeatureByName( 'campaigns.test_active.group2' )->getRules();

		$this->assertCount( 0, $features->getFeatureByName( 'campaigns.test_active.group1' )->getRules(), 'Default group feature should have no rules' );

		$this->assertEquals( new Percentage( 50 ), $rules[2] );
	}

	public function testWhenCampaignWithFourGroupsIsActive_AllFeaturesExceptTheDefaultHavePercentagesBasedOnNumberOfGroups() {
		$factory = new CampaignFeatureBuilder( new Campaign(
			'test',
			't',
			new \DateTime( '2000-01-01' ),
			new \DateTime( '2099-01-01' ),
			Campaign::ACTIVE,
			'group1',
			[ 'group1', 'group2', 'group3', 'group4' ]
		) );
		$expectedRule = new Percentage( 25 );

		$features = $factory->getFeatures();

		$this->assertCount( 0, $features->getFeatureByName( 'campaigns.test.group1' )->getRules(), 'Default group feature should have no rules' );
		$this->assertEquals( $expectedRule, $features->getFeatureByName( 'campaigns.test.group2' )->getRules()[2] );
		$this->assertEquals( $expectedRule, $features->getFeatureByName( 'campaigns.test.group3' )->getRules()[2] );
		$this->assertEquals( $expectedRule, $features->getFeatureByName( 'campaigns.test.group4' )->getRules()[2] );
	}

	private function newInactiveCampaign(): Campaign {
		return new Campaign(
			'test_inactive',
			't',
			new \DateTime( '2000-01-01' ),
			new \DateTime( '2099-01-01' ),
			Campaign::INACTIVE,
			'group1',
			[ 'group1', 'group2' ]
		);
	}

	private function newActiveCampaign(): Campaign {
		return new Campaign(
			'test_active',
			't',
			new \DateTime( '2000-01-01' ),
			new \DateTime( '2099-01-01' ),
			Campaign::ACTIVE,
			'group1',
			[ 'group1', 'group2' ]
		);
	}


}
