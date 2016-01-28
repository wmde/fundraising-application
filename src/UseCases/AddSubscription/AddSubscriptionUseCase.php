<?php


namespace WMDE\Fundraising\Frontend\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase {

	/**
	 * @var SubscriptionRepository
	 */
	private $requestRepository;

	private $requestValidator;

	public function __construct( SubscriptionRepository $requestRepository, SubscriptionValidator $requestValidator ) {
		$this->requestRepository = $requestRepository;
		$this->requestValidator = $requestValidator;
	}

	public function addSubscription( SubscriptionRequest $subscriptionRequest ) {
		$request = $this->createSubscriptionFromRequest( $subscriptionRequest );

		if ( ! $this->requestValidator->validate( $request ) ) {
			return ValidationResponse::newFailureResponse( $this->requestValidator->getConstraintViolations() );
		}
		$this->requestRepository->storeSubscription( $request );

		// TODO send mails

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