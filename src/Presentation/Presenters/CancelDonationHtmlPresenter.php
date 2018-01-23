<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\UseCases\CancelDonation\CancelDonationResponse;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Present the result of the donation cancellation request
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
			'cancellationSuccessful' => $response->cancellationSucceeded(),
			'mailDeliveryFailed' => $response->mailDeliveryFailed()
		] );
	}

}
