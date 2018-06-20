<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\Campaign;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Group;

class CampaignTest extends TestCase {

	public function testBucketsAddedGetAnIndexInTheOrderTheyWereAdded() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstGroup = new Group( 'default', $campaign, Group::DEFAULT );
		$secondGroup = new Group( 'variant_1', $campaign, Group::NON_DEFAULT );
		$thirdGroup = new Group( 'variant_2', $campaign, Group::NON_DEFAULT );

		$campaign->addGroup( $firstGroup )->addGroup( $secondGroup )->addGroup( $thirdGroup );

		$this->assertSame( $firstGroup, $campaign->getGroupByIndex( 0 ) );
		$this->assertSame( $secondGroup, $campaign->getGroupByIndex( 1 ) );
		$this->assertSame( $thirdGroup, $campaign->getGroupByIndex( 2 ) );
	}

	public function testCampaignsCanReturnIndexesForBuckets() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstGroup = new Group( 'default', $campaign, Group::DEFAULT );
		$secondGroup = new Group( 'variant_1', $campaign, Group::NON_DEFAULT );
		$thirdGroup = new Group( 'variant_2', $campaign, Group::NON_DEFAULT );

		$campaign->addGroup( $firstGroup )->addGroup( $secondGroup )->addGroup( $thirdGroup );

		$this->assertSame( 0, $campaign->getIndexByGroup( $firstGroup ) );
		$this->assertSame( 1, $campaign->getIndexByGroup( $secondGroup ) );
		$this->assertSame( 2, $campaign->getIndexByGroup( $thirdGroup ) );
	}

	public function testGivenABucketThatIsNotAddedToCampaigns_campaignWillThrowAnException() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );
		$firstGroup = new Group( 'default', $campaign, Group::DEFAULT );
		$secondGroup = new Group( 'variant_1', $campaign, Group::NON_DEFAULT );
		$thirdGroup = new Group( 'variant_2', $campaign, Group::NON_DEFAULT );

		$campaign->addGroup( $firstGroup )->addGroup( $secondGroup );

		$this->expectException( \OutOfBoundsException::class );
		$campaign->getIndexByGroup( $thirdGroup );
	}

	public function testGivenAnUnknownIndex_campaignWillReturnNull() {
		$campaign = new Campaign( 'test', 't', new \DateTime(), new \DateTime(), true );

		$this->assertNull( $campaign->getGroupByIndex( 0 ) );
	}
}
