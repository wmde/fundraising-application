<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription;

use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionUseCase {

	private $subscriptionRepository;

	/**
	 * @var TemplateBasedMailer
	 */
	private $mailer;

	public function __construct( SubscriptionRepository $subscriptionRepository, TemplateBasedMailer $mailer ) {
		$this->subscriptionRepository = $subscriptionRepository;
		$this->mailer = $mailer;
	}

	public function confirmSubscription( string $confirmationCode ): ValidationResponse {
		$binaryConfirmationCode = hex2bin( $confirmationCode );
		$subscription = $this->subscriptionRepository->findByConfirmationCode( $binaryConfirmationCode );
		if ( ! $subscription ) {
			$errorMsg = 'No subscription was found with this confirmation code.';
			return ValidationResponse::newFailureResponse( [ new ConstraintViolation( $confirmationCode, $errorMsg ) ] );
		}

		// TODO if $subscription->getState != unconfirmed -> fail

		$this->mailer->sendMail( new MailAddress( $subscription->getEmail() ), [ 'subscription' => $subscription ] );
		return ValidationResponse::newSuccessResponse();
	}
}