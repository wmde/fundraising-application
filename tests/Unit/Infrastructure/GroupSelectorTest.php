<?php
/**
 * Created by IntelliJ IDEA.
 * User: tozh
 * Date: 19.06.18
 * Time: 12:11
 */

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Campaign;
use WMDE\Fundraising\Frontend\Infrastructure\CampaignCollection;
use WMDE\Fundraising\Frontend\Infrastructure\Group;
use WMDE\Fundraising\Frontend\Infrastructure\GroupSelector;

class GroupSelectorTest extends TestCase {

	private function newCampaign() {
		return new Campaign(
			'test1',
			't1',
			new \DateTime(),
			new \DateTime(),
			Campaign::ACTIVE
		);
	}

	public function testGivenNoCampaigns_getGroupNamesReturnsEmptyArray() {
		$this->assertSame( [], ( new GroupSelector ( new CampaignCollection(), [], [] ) )->selectGroups() );
	}

	public function testGivenMatchingUrlParams_groupIsSelected() {
		$campaign1 = $this->newCampaign();
		$groupA = new Group( 'a', $campaign1, Group::DEFAULT );
		$groupB = new Group( 'b', $campaign1, Group::NON_DEFAULT );
		$campaign1->addGroup( $groupA )->addGroup( $groupB );
		$this->assertSame(
			[ $groupA ],
			( new GroupSelector (
				new CampaignCollection( $campaign1 ),
				[],
				[ 't1' => 0 ]
			) )->selectGroups()
		);
	}

	public function testGivenMatchingCookieParams_groupIsSelected() {
		$campaign1 = $this->newCampaign();
		$groupA = new Group( 'a', $campaign1, Group::DEFAULT );
		$groupB = new Group( 'b', $campaign1, Group::NON_DEFAULT );
		$campaign1->addGroup( $groupA )->addGroup( $groupB );
		$this->assertSame(
			[ $groupA ],
			( new GroupSelector (
				new CampaignCollection( $campaign1 ),
				[ 't1' => 0 ],
				[]
			) )->selectGroups()
		);
	}

	public function testGivenNoParams_groupIsRandomlySelected() {
		$campaign1 = $this->newCampaign();
		$groupA = new Group( 'a', $campaign1, Group::DEFAULT );
		$groupB = new Group( 'b', $campaign1, Group::NON_DEFAULT );
		$campaign1->addGroup( $groupA )->addGroup( $groupB );
		$this->assertThat(
			( new GroupSelector (
				new CampaignCollection( $campaign1 ),
				[],
				[]
			) )->selectGroups(),
			$this->logicalOr(
				$this->equalTo( [ $groupA ] ),
				$this->equalTo( [ $groupB ] )
			)
		);
	}

}
