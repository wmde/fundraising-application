<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationPageSelectorTest extends \PHPUnit\Framework\TestCase {

	public function testWhenConfigIsEmpty_selectPageReturnsDefaultPageTitle(): void {
		$selector = new DonationConfirmationPageSelector( $this->newCampaignConfig( [] ) );
		$this->assertSame( '', $selector->selectPage()->getCampaignCode() );
		$this->assertSame( 'defaultConfirmationPage', $selector->selectPage()->getPageTitle() );
	}

	public function testWhenConfigContainsOneElement_selectPageReturnsThatElement(): void {
		$selector = new DonationConfirmationPageSelector( $this->newCampaignConfig( [ 'ThisIsJustATest.twig' ] ) );
		$this->assertSame( 'example', $selector->selectPage()->getCampaignCode() );
		$this->assertSame( 'ThisIsJustATest.twig', $selector->selectPage()->getPageTitle() );
	}

	public function testWhenConfigContainsSomeElements_selectPageReturnsOne(): void {
		$selector = new DonationConfirmationPageSelector(
			$this->newCampaignConfig( [ 'ThisIsJustATest.twig', 'AnotherOne.twig' ] )
		);
		$this->assertSame( 'example', $selector->selectPage()->getCampaignCode() );
		$this->assertNotEmpty( $selector->selectPage()->getPageTitle() );
	}

	private function newCampaignConfig( array $templates ): array {
		return [
			'default' => 'defaultConfirmationPage',
			'campaigns' => [
				[
					'code' => 'example',
					'active' => true,
					'startDate' => '1970-01-01 00:00:00',
					'endDate' => '2038-12-31 23:59:59',
					'templates' => $templates
				]
			]
		];
	}

}
