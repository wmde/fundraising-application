<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\DonationPaymentValidator;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;

class NewDonationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		$amount = intval( $request->get( 'amount', 0 ) );
		$paymentType = (string)$request->get( 'paymentType', '' );
		$interval = $request->get( 'interval', 0 );
		if ( $interval !== null ) {
			$interval = intval( $interval );
		}

		$validationResult = $ffFactory->newPaymentValidator()->validatePaymentData( $amount, $interval, $paymentType, new DonationPaymentValidator() );

		$trackingInfo = new ImpressionCounts(
			intval( $request->get( 'impCount' ) ),
			intval( $request->get( 'bImpCount' ) )
		);

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$paymentType,
				$interval,
				$validationResult->isSuccessful(),
				$trackingInfo,
				$request->get( 'addressType', $ffFactory->getAddressType() ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}
}
