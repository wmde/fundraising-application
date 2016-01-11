<?php


namespace WMDE\Fundraising\Frontend\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\RequestValidator;
use WMDE\Fundraising\Frontend\MailValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCase
{
	public function addSubscription( SubscriptionRequest $subscriptionRequest ) {
		$request = $this->createRequestFromSubscriptionRequest( $subscriptionRequest );
		$validator = new RequestValidator( new MailValidator( MailValidator::TEST_WITH_MX ) );
		if ( ! $validator->validate( $request ) ) {
			return AddSubscriptionResponse::createInvalidResponse( $request, $validator->getValidationErrors() );
		}
		// TODO store in DB
		// TODO send mails
		return AddSubscriptionResponse::createValidResponse( $request );
	}

	private function createRequestFromSubscriptionRequest( SubscriptionRequest $subscriptionRequest ): Request {
		$request = new Request();
		$request->setAnrede( $subscriptionRequest->getSalutation() );
		$request->setTitel( $subscriptionRequest->getTitle() );
		$request->setName( $subscriptionRequest->getFirstName() );
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