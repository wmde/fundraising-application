<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationResponse;

/**
 * Render the credit card payment page embedding an iframe
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CancelDonationHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( CancelDonationResponse $response ): string {
		return $this->template->render( [
			'donationId' => $response->getDonationId(),
			'cancellationSuccessful' => $response->cancellationWasSuccessful()
		] );
	}

}
