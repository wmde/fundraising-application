<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

class ShowMembershipFeeUpgradeController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );

		//TODO check for the validity of the UUID (and email?)


		return new Response(
			$ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
				[
					'urls' => Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
					'presetAmounts' => $ffFactory->getPresetAmountsSettings('membership'),
					'paymentIntervals' => $ffFactory->getMembershipPaymentIntervals(),
				]
			)
		);

		//TODO if validation fails: render error page
		// return $this->errorResponse( $response->getErrorMessage(), Response::HTTP_OK );
	}
}