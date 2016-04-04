<?php

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SelectedConfirmationPage {

	private $campaignCode;
	private $pageTitle;

	public function __construct( string $campaignCode, string $pageTitle ) {
		$this->campaignCode = $campaignCode;
		$this->pageTitle = $pageTitle;
	}

	public function getCampaignCode(): string {
		return $this->campaignCode;
	}

	public function getPageTitle(): string {
		return $this->pageTitle;
	}

}