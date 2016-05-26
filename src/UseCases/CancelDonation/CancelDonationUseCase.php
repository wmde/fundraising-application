<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCase {

	private $donationRepository;
	private $mailer;
	private $authorizationService;

	public function __construct( DonationRepository $donationRepository, TemplateBasedMailer $mailer,
		DonationAuthorizer $authorizationService ) {

		$this->donationRepository = $donationRepository;
		$this->mailer = $mailer;
		$this->authorizationService = $authorizationService;
	}

	public function cancelDonation( CancelDonationRequest $cancellationRequest ): CancelDonationResponse {
		if ( !$this->authorizationService->userCanModifyDonation( $cancellationRequest->getDonationId() ) ) {
			return $this->newFailureResponse( $cancellationRequest );
		}

		try {
			$donation = $this->donationRepository->getDonationById( $cancellationRequest->getDonationId() );
		}
		catch ( GetDonationException $ex ) {
			return $this->newFailureResponse( $cancellationRequest );
		}

		if ( $donation === null ) {
			return $this->newFailureResponse( $cancellationRequest );
		}

		try {
			$donation->cancel();
		}
		catch ( \RuntimeException $ex ) {
			return $this->newFailureResponse( $cancellationRequest );
		}

		// TODO: update donation status
		// TODO: add log message to spenden.data['log']

		$this->sendConfirmationEmail( $donation );

		return $this->newSuccessResponse( $cancellationRequest );
	}

	private function newSuccessResponse( CancelDonationRequest $cancellationRequest ): CancelDonationResponse {
		return new CancelDonationResponse( $cancellationRequest->getDonationId(), true );
	}

	private function newFailureResponse( CancelDonationRequest $cancellationRequest ): CancelDonationResponse {
		return new CancelDonationResponse( $cancellationRequest->getDonationId(), false );
	}

	private function sendConfirmationEmail( Donation $donation ) {
		if ( $donation->getDonor() !== null ) {
			$this->mailer->sendMail(
				new EmailAddress( $donation->getDonor()->getEmailAddress() ),
				$this->getConfirmationMailTemplateArguments( $donation )
			);
		}
	}

	private function getConfirmationMailTemplateArguments( Donation $donation ): array {
		return [
			'donationId' => $donation->getId(),

			'recipient' => [
				'lastName' => $donation->getDonor()->getPersonName()->getLastName(),
				'salutation' =>	$donation->getDonor()->getPersonName()->getSalutation(),
				'title' => $donation->getDonor()->getPersonName()->getTitle()
			],
		];
	}

}
