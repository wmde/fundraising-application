<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCase {

	private const LOG_MESSAGE_FOR_BACKEND = 'frontend: storno';

	private $donationRepository;
	private $mailer;
	private $authorizationService;
	private $donationLogger;

	public function __construct( DonationRepository $donationRepository, TemplateMailerInterface $mailer,
		DonationAuthorizer $authorizationService, DonationEventLogger $donationLogger ) {

		$this->donationRepository = $donationRepository;
		$this->mailer = $mailer;
		$this->authorizationService = $authorizationService;
		$this->donationLogger = $donationLogger;
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

		try {
			$this->donationRepository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return $this->newFailureResponse( $cancellationRequest );
		}

		$this->donationLogger->log( $donation->getId(), self::LOG_MESSAGE_FOR_BACKEND );

		try {
			$this->sendConfirmationEmail( $donation );
		}
		catch ( \RuntimeException $ex ) {
			return new CancelDonationResponse(
				$cancellationRequest->getDonationId(),
				CancelDonationResponse::MAIL_DELIVERY_FAILED
			);
		}

		return $this->newSuccessResponse( $cancellationRequest );
	}

	private function newFailureResponse( CancelDonationRequest $cancellationRequest ): CancelDonationResponse {
		return new CancelDonationResponse(
			$cancellationRequest->getDonationId(),
			CancelDonationResponse::FAILURE
		);
	}

	private function newSuccessResponse( CancelDonationRequest $cancellationRequest ): CancelDonationResponse {
		return new CancelDonationResponse(
			$cancellationRequest->getDonationId(),
			CancelDonationResponse::SUCCESS
		);
	}

	private function sendConfirmationEmail( Donation $donation ): void {
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
				'lastName' => $donation->getDonor()->getName()->getLastName(),
				'salutation' =>	$donation->getDonor()->getName()->getSalutation(),
				'title' => $donation->getDonor()->getName()->getTitle()
			],
		];
	}

}
