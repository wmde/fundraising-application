<?php

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateTestCampaign;

/**
 * Selects a random confirmation page from given options
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationPageSelector {

	private $defaultPageTitle;
	/** @var TemplateTestCampaign[] */
	private $campaigns;

	public function __construct( array $config ) {
		$this->defaultPageTitle = $config['default'];
		$this->campaigns = $config['campaigns'];
	}

	public function selectPage(): string {
		foreach ( $this->getRunningCampaigns() as $campaign ) {
			if ( !empty( $campaign->getTemplates() ) ) {
				return $campaign->getRandomTemplate();
			}
		}

		return $this->defaultPageTitle;
	}

	/**
	 * @return TemplateTestCampaign[]
	 */
	public function getRunningCampaigns(): array {
		return array_filter(
			$this->campaigns,
			function( TemplateTestCampaign $campaign ) {
				return $campaign->isRunning();
			}
		);
	}

}
