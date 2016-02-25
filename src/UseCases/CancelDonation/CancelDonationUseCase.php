<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCase {

	private $donationRepository;
	private $mailer;

	public function __construct( DonationRepository $donationRepository, TemplateBasedMailer $mailer ) {
		$this->donationRepository = $donationRepository;
		$this->mailer = $mailer;
	}

	public function cancelDonation( CancelDonationRequest $cancellationRequest ) {
		// TODO: update donation status
		// TODO: add log message to spenden.data['log']
		// TODO: reset spenden_stamp in cookie
		// TODO: send cancellation confirmation email
	}

}
