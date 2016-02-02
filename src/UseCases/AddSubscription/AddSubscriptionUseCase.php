<?php


namespace WMDE\Fundraising\Frontend\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\TemplatedMessage;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase {

	/**
	 * @var SubscriptionRepository
	 */
	private $subscriptionRepository;

	private $subscriptionValidator;

	private $messenger;

	public function __construct( SubscriptionRepository $subscriptionRepository, SubscriptionValidator $subscriptionValidator,
								 Messenger $messenger, TemplatedMessage $message ) {

		$this->subscriptionRepository = $subscriptionRepository;
		$this->subscriptionValidator = $subscriptionValidator;
		$this->messenger = $messenger;
		$this->message = $message;
	}

	public function addSubscription( SubscriptionRequest $subscriptionRequest ) {
		$subscription = $this->createSubscriptionFromRequest( $subscriptionRequest );

		if ( ! $this->subscriptionValidator->validate( $subscription ) ) {
			return ValidationResponse::newFailureResponse( $this->subscriptionValidator->getConstraintViolations() );
		}
		$this->subscriptionRepository->storeSubscription( $subscription );

		$postalAddress = $subscription->getAddress();
		$this->message->setTemplateParams( [ 'subscription' => $subscription ] );
		$this->messenger->sendMessage( $this->message,
			new MailAddress(
				$subscription->getEmail(),
				implode( ' ', [ $postalAddress->getFirstName(), $postalAddress->getLastName() ] )
			)
		);

		return ValidationResponse::newSuccessResponse();
	}

	private function createSubscriptionFromRequest( SubscriptionRequest $subscriptionRequest ): Subscription {
		$request = new Subscription();
		$address = new Address();
		$address->setSalutation( $subscriptionRequest->getSalutation() );
		$address->setTitle( $subscriptionRequest->getTitle() );
		$address->setFirstName( $subscriptionRequest->getFirstName() );
		$address->setLastName( $subscriptionRequest->getLastName() );
		$address->setAddress( $subscriptionRequest->getAddress() );
		$address->setPostcode( $subscriptionRequest->getPostcode() );
		$address->setCity( $subscriptionRequest->getCity() );

		$request->setAddress( $address );
		$request->setEmail( $subscriptionRequest->getEmail() );
		$request->setConfirmationCode( random_bytes( 16 ) ); // No need to use uuid library here
		return $request;
	}
}