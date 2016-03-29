<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateTestCampaign;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\TemplateTestCampaign
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TemplateTestCampaignTest extends \PHPUnit_Framework_TestCase {

	public function testConstructorSetsFields() {
		$campaign = new TemplateTestCampaign( [
			'code' => 'FOO',
			'active' => true,
			'startDate' => '2015-11-01 08:00:00',
			'endDate' => '2015-11-08 08:00:00',
			'templates' => [
				'bar',
				'baz'
			]
		] );

		$this->assertEquals( 'FOO', $campaign->getCode() );
		$this->assertTrue( $campaign->isActive() );
		$this->assertEquals( '2015-11-01 08:00:00', $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2015-11-08 08:00:00', $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( [ 'bar', 'baz' ], $campaign->getTemplates() );
	}

	private function newCampaign( array $campaignData ) {
		$campaignTemplate = [
			'code' => 'FOO',
			'active' => true,
			'startDate' => '2015-11-01 08:00:00',
			'endDate' => '2015-11-08 08:00:00',
			'templates' => [
				'bar',
				'baz'
			]
		];
		return new TemplateTestCampaign( array_merge( $campaignTemplate, $campaignData ) );
	}

	public function testHasStarted() {
		$startedCampaign = $this->newCampaign( [
			'startDate' => '2015-01-01 08:00:00',
			'endDate' => '2115-01-01 08:00:00',
		] );
		$notStartedCampaign = $this->newCampaign( [
			'startDate' => '2115-01-01 08:00:00',
			'endDate' => '2115-08-01 08:00:00',
		] );
		$this->assertTrue( $startedCampaign->hasStarted() );
		$this->assertFalse( $notStartedCampaign->hasStarted() );

	}

	public function testHasEnded() {
		$finishedCampaign = $this->newCampaign( [
			'startDate' => '2015-01-01 08:00:00',
			'endDate' => '2015-09-01 08:00:00',
		] );
		$notFinishedCampaign = $this->newCampaign( [
			'startDate' => '2015-01-01 08:00:00',
			'endDate' => '2115-01-01 08:00:00',
		] );
		$this->assertTrue( $finishedCampaign->hasEnded() );
		$this->assertFalse( $notFinishedCampaign->hasEnded() );
	}

	public function testIsRunningDataProvider() {
		return [
			[
				true,
				[
					'active' => true,
					'startDate' => '2015-01-01 08:00:00',
					'endDate' => '2115-01-01 08:00:00',
				]
			],
			[
				false,
				[
					'active' => true,
					'startDate' => '2115-01-01 08:00:00',
					'endDate' => '2115-08-01 08:00:00',
				]
			],
			[
				false,
				[
					'active' => true,
					'startDate' => '2005-01-01 08:00:00',
					'endDate' => '2005-08-01 08:00:00',
				]
			],
			[
				false,
				[
					'active' => false,
					'startDate' => '2015-01-01 08:00:00',
					'endDate' => '2115-01-01 08:00:00',
				]
			],
		];
	}

	/**
	 * @dataProvider testIsRunningDataProvider
	 */
	public function testIsRunning( $expected, array $data ) {
		$campaign = $this->newCampaign( $data );
		$this->assertEquals( $expected, $campaign->isRunning() );
	}

}
