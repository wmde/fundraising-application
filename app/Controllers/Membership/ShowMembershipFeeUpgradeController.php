<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * Serves the following feature: A user is asked if he/she wants to update their membership fee on an external form ( not
 * showing the regular membership form).
 */
class ShowMembershipFeeUpgradeController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$uuidFromRequest = $request->query->get( 'uuid', '' );

		$htmlPresenter = $ffFactory->newMembershipFeeUpgradeHTMLPresenter();

		$feeUpgradeUseCase = $ffFactory->newMembershipFeeUpgradeUseCase();
		$feeUpgradeUseCase->showFeeChange( $uuidFromRequest, $htmlPresenter );

		return $htmlPresenter->getHTMLResponse();
	}
}
