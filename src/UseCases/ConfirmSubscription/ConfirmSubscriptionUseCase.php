<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription;

use WMDE\Fundraising\Entities\Subscription;
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

	private $mailer;

	public function __construct( SubscriptionRepository $subscriptionRepository, TemplateBasedMailer $mailer ) {
		$this->subscriptionRepository = $subscriptionRepository;
		$this->mailer = $mailer;
	}

	public function confirmSubscription( string $confirmationCode ): ValidationResponse {

		$subscription = $this->subscriptionRepository->findByConfirmationCode( $confirmationCode );

		if ( $subscription === null ) {
			$errorMsg = 'No subscription was found with this confirmation code.';
			return ValidationResponse::newFailureResponse( [ new ConstraintViolation( $confirmationCode, $errorMsg ) ] );
		}

		if ( $subscription->getStatus() === Subscription::STATUS_NEUTRAL ) {
			$subscription->setStatus( Subscription::STATUS_CONFIRMED );
			$this->subscriptionRepository->storeSubscription( $subscription );
			$this->mailer->sendMail( new MailAddress( $subscription->getEmail() ), [ 'subscription' => $subscription ] );
			return ValidationResponse::newSuccessResponse();
		}

		$errorMsg = 'The subscription was already confirmed.';
		return ValidationResponse::newFailureResponse( [ new ConstraintViolation( $confirmationCode, $errorMsg ) ] );
	}
}