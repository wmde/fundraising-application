<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Controllers\API\Donation\AbstractApiController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\FeeChangeRequest;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\FeeChangeUseCase;

class UpgradeMembershipFeeController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {

		//TODO check that all JSON fields exist and have the correct type

		//TODO build the DTO
		$feeChangeRequest = new FeeChangeRequest(
			uuid: null,
			amount: null,
			email: null,
			interval: null
		);
		$feeChangeUseCase = new FeeChangeUseCase( $ffFactory->getFeeChangeRepository() );
		$useCaseResponse = $feeChangeUseCase->changeFee( $feeChangeRequest );

		if( $useCaseResponse->success ) {

		}
		//TODO if it errors: log the error and return a JSON error result
		// Use a format similar to the /validate-fee route, so we can reuse code for displaying error messages on the frontend,
	}
}