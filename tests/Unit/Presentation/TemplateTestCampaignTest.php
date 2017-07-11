<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\TemplateTestCampaign;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\TemplateTestCampaign
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TemplateTestCampaignTest extends \PHPUnit\Framework\TestCase {

	public function testConstructorSetsFields(): void {
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

		$this->assertSame( 'FOO', $campaign->getCode() );
		$this->assertTrue( $campaign->isActive() );
		$this->assertSame( '2015-11-01 08:00:00', $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( '2015-11-08 08:00:00', $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( [ 'bar', 'baz' ], $campaign->getTemplates() );
	}

	private function newCampaign( array $campaignData ): TemplateTestCampaign {
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

	public function testHasStarted(): void {
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

	public function testHasEnded(): void {
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

	/**
	 * @dataProvider isRunningDataProvider
	 */
	public function testIsRunning( bool $expected, array $data ): void {
		$campaign = $this->newCampaign( $data );
		$this->assertSame( $expected, $campaign->isRunning() );
	}

	public function isRunningDataProvider(): array {
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

}
