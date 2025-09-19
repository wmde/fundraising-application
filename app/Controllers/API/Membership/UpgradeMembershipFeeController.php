<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Membership;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\FeeChangeRequest;

class UpgradeMembershipFeeController {

	public function index( FunFunFactory $ffFactory, #[MapRequestPayload] FeeChangeRequest $feeChangeRequest ): JSONResponse {
		$feeUpgradeUseCase = $ffFactory->newMembershipFeeUpgradeUseCase();
		$feeChangeResponse = $feeUpgradeUseCase->changeFee( $feeChangeRequest );

		if ( $feeChangeResponse->success ) {
			return new JsonResponse(
				data: [ 'status' => 'OK' ],
				status: 200
			);
		}

		$ffFactory->getLogger()->error(
			'Fee change failed for UUID. ',
			[
				'uuid' => $feeChangeRequest->uuid,
				'amount' => $feeChangeRequest->amountInEuroCents,
				'paymentType' => $feeChangeRequest->paymentType,
				'validationResult' => $feeChangeResponse->validationResult
			]
		);
		return new JsonResponse(
			data: [
				'status' => 'ERR',
				'errors' => $feeChangeResponse->validationResult
			],
			status: 200
		);
	}
}
