<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\Campaign;
use WMDE\Fundraising\Frontend\Infrastructure\CampaignCollection;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Group;

class CampaignCollectionTest extends TestCase {
	public function testGivenValidUrlAndValue_itReturnsGroup() {
		$campaign1 = new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$groupA = new Group( 'a', $campaign1, Group::DEFAULT );
		$groupB = new Group( 'b', $campaign1, Group::NON_DEFAULT );
		$campaign1->addGroup( $groupA)->addGroup($groupB);
		$campaign2 = new Campaign(
			'test2',
			't2',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
		$groupC = new Group( 'c', $campaign1, Group::DEFAULT );
		$groupD = new Group( 'd', $campaign1, Group::NON_DEFAULT );
		$campaign2->addGroup( $groupC)->addGroup($groupD);

		$collection = new CampaignCollection( $campaign1, $campaign2 );

		$this->assertEquals([[$groupA], [$campaign2]], $collection->splitGroupsFromCampaigns( [ 't1'=> 0 ] ) );
		$this->assertEquals([[$groupA, $groupD],[] ], $collection->splitGroupsFromCampaigns( [ 't1'=> 0, 't2' => 1 ] ) );
	}

	//TODO
	public function testGivenInvalidUrlValue_itReturnsCampaigns() {
		$this->markTestIncomplete('not implemented yet');
	}




}
