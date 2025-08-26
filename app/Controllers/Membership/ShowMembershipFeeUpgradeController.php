<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\FeeChangeUseCase;

class ShowMembershipFeeUpgradeController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$uuidFromRequest = $request->query->get('uuid', '');

		$feeChangeUseCase = new FeeChangeUseCase( $ffFactory->getFeeChangeRepository() );

		if( $feeChangeUseCase->feeChangeExists( $uuidFromRequest, FeeChanges::EMAIL) ) {
			return new Response(
				$ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
					[
						'urls' => Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
						'presetAmounts' => $ffFactory->getPresetAmountsSettings( 'membership' ),
						'paymentIntervals' => $ffFactory->getMembershipPaymentIntervals(),
						'uuid' => $uuidFromRequest
					]
				)
			);
		}

		//TODO proper message
		return new Response(
			$ffFactory->newErrorPageHtmlPresenter()->present( "TODO proper error message!!!!!" ),
			Response::HTTP_NOT_FOUND
		);
	}
}