<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Membership;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\App\Controllers\API\Donation\AbstractApiController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\MembershipApplicantDataFormatter;
use WMDE\Fundraising\MembershipContext\UseCases\UpdateMembershipApplication\UpdateMembershipApplicationRequest;

class UpdateMembershipApplicationController extends AbstractApiController {

	private const MESSAGE_EMPTY_BODY = 'update_membership_empty_request_body';
	private const MESSAGE_FAILED = 'update_membership_failed';

	public function index( Request $request, FunFunFactory $ffFactory, string $accessToken ): Response {
		$data = new ParameterBag( $request->toArray() );

		if ( $data->count() === 0 ) {
			return $this->errorResponse( self::MESSAGE_EMPTY_BODY, Response::HTTP_BAD_REQUEST );
		}

		$responseModel = $ffFactory
			->newUpdateMembershipApplicationUseCase( $data->get( 'updateToken', '' ), $accessToken )
			->updateMembershipApplication( $this->newRequestModel( $data ) );

		if ( !$responseModel->isSuccessful() ) {
			return $this->errorResponse( self::MESSAGE_FAILED, Response::HTTP_BAD_REQUEST, [ $responseModel->getErrorMessage() ] );
		}

		$application = $ffFactory
			->getMembershipApplicationRepository()
			->getMembershipApplicationById( $data->getInt( 'membershipId' ) );

		if ( $application === null ) {
			return $this->errorResponse( self::MESSAGE_FAILED, Response::HTTP_BAD_REQUEST );
		}

		return new JsonResponse( new MembershipApplicantDataFormatter()->getAddressArguments( $application ) );
	}

	private function newRequestModel( ParameterBag $params ): UpdateMembershipApplicationRequest {
		return new UpdateMembershipApplicationRequest(
			$params->getInt( 'membershipId' ),
			$params->get( 'addressType', '' ) === 'firma',
			$params->get( 'salutation', '' ),
			$params->get( 'title', '' ),
			$params->get( 'firstName', '' ),
			$params->get( 'lastName', '' ),
			$params->get( 'companyName', '' ),
			$params->get( 'street', '' ),
			$params->get( 'postcode', '' ),
			$params->get( 'city', '' ),
			$params->get( 'country', '' ),
			new EmailAddress( $params->get( 'email', '' ) )
		);
	}
}
