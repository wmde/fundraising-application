<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\FeeChangeRequest;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

class UpgradeMembershipFeeController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {

		//TODO check that all JSON fields exist and have the correct type
		$uuidFromRequest = $request->query->get('uuid', '');
		$amountInEurocentsFromRequest = $request->query->get('amountInEuroCents', 0);
		$paymentTypeFromRequest = $request->query->get('paymentType', '');
		$ibanFromRequest = $request->query->get('iban', '');
		$bicFromRequest = $request->query->get('bic', '');

		if( !ctype_digit( $amountInEurocentsFromRequest ) ) {
			//TODO return error
		}

		if( PaymentType::tryFrom($paymentTypeFromRequest) === null ){
			//TODO return error
		}

		//TODO build the DTO
		$feeChangeRequest = new FeeChangeRequest(
			uuid: $uuidFromRequest,
			amountInEuroCents: $amountInEurocentsFromRequest,
			paymentType: PaymentType::from( $paymentTypeFromRequest)->value,
			iban: $ibanFromRequest,
			bic: $bicFromRequest
		);

		$feeUpgradeUseCase = $ffFactory->newMembershipFeeUpgradeUseCase();
		$feeChangeResponse = $feeUpgradeUseCase->changeFee( $feeChangeRequest );

		if( $feeChangeResponse->success ){
			//api call?
		}


		//TODO if it errors: log the error and return a JSON error result
		// Use a format similar to the /validate-fee route, so we can reuse code for displaying error messages on the frontend,
		return new Response('');
	}
}