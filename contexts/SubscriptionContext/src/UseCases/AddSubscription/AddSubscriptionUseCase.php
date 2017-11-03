<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\EmailAddress;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepositoryException;
use WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionValidator;
use WMDE\FunValidators\ValidationResponse;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase {

	private const CONFIRMATION_CODE_LENGTH_BYTES = 16;

	private $subscriptionRepository;
	private $subscriptionValidator;
	private $mailer;

	public function __construct( SubscriptionRepository $subscriptionRepository,
		SubscriptionValidator $subscriptionValidator, TemplateMailerInterface $mailer ) {

		$this->subscriptionRepository = $subscriptionRepository;
		$this->subscriptionValidator = $subscriptionValidator;
		$this->mailer = $mailer;
	}

	/**
	 * @param SubscriptionRequest $subscriptionRequest
	 * @return ValidationResponse
	 * @throws SubscriptionRepositoryException
	 */
	public function addSubscription( SubscriptionRequest $subscriptionRequest ): ValidationResponse {
		$subscription = $this->createSubscriptionFromRequest( $subscriptionRequest );

		$validationResult = $this->subscriptionValidator->validate( $subscription );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		if ( $this->subscriptionValidator->needsModeration( $subscription ) ) {
			$subscription->markForModeration();
		}

		$this->subscriptionRepository->storeSubscription( $subscription );

		if ( $this->subscriptionValidator->needsModeration( $subscription ) ) {
			return ValidationResponse::newModerationNeededResponse();
		}

		$this->sendSubscriptionNotification( $subscription );

		return ValidationResponse::newSuccessResponse();
	}

	private function sendSubscriptionNotification( Subscription $subscription ): void {
		$this->mailer->sendMail(
			$this->newMailAddressFromSubscription( $subscription ),
			// FIXME: this is an output similar to the main response model and should similarly not be an entity
			[ 'subscription' => $subscription ]
		);
	}

	private function newMailAddressFromSubscription( Subscription $subscription ): EmailAddress {
		return new EmailAddress( $subscription->getEmail() );
	}

	private function createSubscriptionFromRequest( SubscriptionRequest $subscriptionRequest ): Subscription {
		$subscription = new Subscription();

		$subscription->setAddress( $this->addressFromSubscriptionRequest( $subscriptionRequest ) );
		$subscription->setEmail( $subscriptionRequest->getEmail() );
		$subscription->setTracking( $subscriptionRequest->getTrackingString() );
		$subscription->setSource( $subscriptionRequest->getSource() );
		$subscription->setConfirmationCode( $this->generateConfirmationCode() );

		return $subscription;
	}

	private function generateConfirmationCode(): string {
		return bin2hex( random_bytes( self::CONFIRMATION_CODE_LENGTH_BYTES ) );
	}

	private function addressFromSubscriptionRequest( SubscriptionRequest $subscriptionRequest ): Address {
		$address = new Address();

		$address->setSalutation( $subscriptionRequest->getSalutation() );
		$address->setTitle( $subscriptionRequest->getTitle() );
		$address->setFirstName( $subscriptionRequest->getFirstName() );
		$address->setLastName( $subscriptionRequest->getLastName() );
		$address->setAddress( $subscriptionRequest->getAddress() );
		$address->setPostcode( $subscriptionRequest->getPostcode() );
		$address->setCity( $subscriptionRequest->getCity() );

		return $address;
	}

}