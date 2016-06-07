<?php

namespace WMDE\Fundraising\Frontend\Presentation;

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
		$this->parseCampaigns( $config['campaigns'] );
	}

	public function selectPage(): SelectedConfirmationPage {
		foreach ( $this->getRunningCampaigns() as $campaign ) {
			if ( !empty( $campaign->getTemplates() ) ) {
				return new SelectedConfirmationPage( $campaign->getCode(), $campaign->getRandomTemplate() );
			}
		}

		return new SelectedConfirmationPage( '', $this->defaultPageTitle );
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

	private function parseCampaigns( $campaigns ) {
		foreach ( $campaigns as $campaign ) {
			$this->campaigns[] = new TemplateTestCampaign( $campaign );
		}
	}

}
