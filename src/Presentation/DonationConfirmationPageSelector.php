<?php

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * Selects a random confirmation page from given options
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationPageSelector {

	private $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function selectPage(): string {
		return empty( $this->config ) ? '' : $this->config[$this->getRandomIndex()];
	}

	private function getRandomIndex() {
		return random_int( 0, count( $this->config ) - 1 );
	}

}
