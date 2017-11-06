<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\ConfirmSubscription;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionUseCase {

	private $subscriptionRepository;

	private $mailer;

	public function __construct( SubscriptionRepository $subscriptionRepository, TemplateMailerInterface $mailer ) {
		$this->subscriptionRepository = $subscriptionRepository;
		$this->mailer = $mailer;
	}

	public function confirmSubscription( string $confirmationCode ): ValidationResponse {

		$subscription = $this->subscriptionRepository->findByConfirmationCode( $confirmationCode );

		if ( $subscription === null ) {
			return ValidationResponse::newFailureResponse( [
				new ConstraintViolation( $confirmationCode, 'subscription_confirmation_code_not_found' )
			] );
		}

		if ( $subscription->isUnconfirmed() ) {
			$subscription->markAsConfirmed();
			$this->subscriptionRepository->storeSubscription( $subscription );
			$this->mailer->sendMail( new EmailAddress( $subscription->getEmail() ), [ 'subscription' => $subscription ] );
			return ValidationResponse::newSuccessResponse();
		}

		return ValidationResponse::newFailureResponse( [
			new ConstraintViolation( $confirmationCode, 'subscription_already_confirmed' )
		] );
	}

}