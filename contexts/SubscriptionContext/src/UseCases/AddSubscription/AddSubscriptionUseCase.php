<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Validation\ValidationResponse;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepositoryException;
use WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase {

	private $subscriptionRepository;
	private $subscriptionValidator;
	private $mailer;

	public function __construct( SubscriptionRepository $subscriptionRepository,
		SubscriptionValidator $subscriptionValidator, TemplateBasedMailer $mailer ) {

		$this->subscriptionRepository = $subscriptionRepository;
		$this->subscriptionValidator = $subscriptionValidator;
		$this->mailer = $mailer;
	}

	/**
	 * @param SubscriptionRequest $subscriptionRequest
	 * @return ValidationResponse
	 * @throws SubscriptionRepositoryException
	 */
	public function addSubscription( SubscriptionRequest $subscriptionRequest ) {
		$subscription = $this->createSubscriptionFromRequest( $subscriptionRequest );

		$validationResult = $this->subscriptionValidator->validate( $subscription );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		if ( $this->subscriptionValidator->needsModeration( $subscription ) ) {
			$subscription->setStatus( Subscription::STATUS_MODERATION );
		}

		$this->subscriptionRepository->storeSubscription( $subscription );

		if ( $this->subscriptionValidator->needsModeration( $subscription ) ) {
			return ValidationResponse::newModerationNeededResponse();
		}

		$this->sendSubscriptionNotification( $subscription );

		return ValidationResponse::newSuccessResponse();
	}

	private function sendSubscriptionNotification( Subscription $subscription ) {
		$this->mailer->sendMail(
			$this->newMailAddressFromSubscription( $subscription ),
			// FIXME: this is an output similar to the main response model and should similarly not be an entity
			[ 'subscription' => $subscription ]
		);
	}

	private function newMailAddressFromSubscription( Subscription $subscription ): EmailAddress {
		return new EmailAddress(
			$subscription->getEmail(),
			implode(
				' ',
				[
					$subscription->getAddress()->getFirstName(),
					$subscription->getAddress()->getLastName()
				]
			)
		);
	}

	private function createSubscriptionFromRequest( SubscriptionRequest $subscriptionRequest ): Subscription {
		$request = new Subscription();

		$request->setAddress( $this->addressFromSubscriptionRequest( $subscriptionRequest ) );
		$request->setEmail( $subscriptionRequest->getEmail() );
		$request->setConfirmationCode( random_bytes( 16 ) ); // No need to use uuid library here

		return $request;
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