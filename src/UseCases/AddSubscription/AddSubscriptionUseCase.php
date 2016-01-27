<?php


namespace WMDE\Fundraising\Frontend\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\RequestRepository;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\RequestValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase {

	/**
	 * @var RequestRepository
	 */
	private $requestRepository;

	private $requestValidator;

	public function __construct( RequestRepository $requestRepository, RequestValidator $requestValidator ) {
		$this->requestRepository = $requestRepository;
		$this->requestValidator = $requestValidator;
	}

	public function addSubscription( SubscriptionRequest $subscriptionRequest ) {
		$request = $this->createRequestFromSubscriptionRequest( $subscriptionRequest );

		if ( ! $this->requestValidator->validate( $request ) ) {
			return ValidationResponse::newFailureResponse( $this->requestValidator->getConstraintViolations() );
		}
		$this->requestRepository->storeRequest( $request );

		// TODO send mails

		return ValidationResponse::newSuccessResponse();
	}

	private function createRequestFromSubscriptionRequest( SubscriptionRequest $subscriptionRequest ): Request {
		$request = new Request();
		$request->setAnrede( $subscriptionRequest->getSalutation() );
		$request->setTitel( $subscriptionRequest->getTitle() );
		$request->setVorname( $subscriptionRequest->getFirstName() );
		$request->setNachname( $subscriptionRequest->getLastName() );
		$request->setEmail( $subscriptionRequest->getEmail() );
		$request->setStrasse( $subscriptionRequest->getAddress() );
		$request->setPlz( $subscriptionRequest->getPostcode() );
		$request->setOrt( $subscriptionRequest->getCity() );
		$request->setWikilogin( $subscriptionRequest->getWikilogin() );
		$request->setGuid( random_bytes( 16 ) ); // No need to use uuid library here
		$request->setType( Request::TYPE_SUBSCRIPTION );
		return $request;
	}
}