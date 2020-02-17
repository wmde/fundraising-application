<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class NewDonationController {

	public function handle( FunFunFactory $ffFactory, Request $request, Application $app ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		try {
			$amount = Euro::newFromCents( intval( $request->get( 'amount', 0 ) ) );
		}
		catch ( \InvalidArgumentException $ex ) {
			$amount = Euro::newFromCents( 0 );
		}

		$validationResult = $ffFactory->newPaymentDataValidator()->validate(
			$amount,
			(string)$request->get( 'paymentType', '' )
		);

		$trackingInfo = new DonationTrackingInfo();
		$trackingInfo->setTotalImpressionCount( intval( $request->get( 'impCount' ) ) );
		$trackingInfo->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount' ) ) );

		// TODO: don't we want to use newDonationFormViolationPresenter when !$validationResult->isSuccessful()?

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$request->get( 'paymentType', '' ),
				intval( $request->get( 'interval', 0 ) ),
				$validationResult->isSuccessful(),
				$trackingInfo,
				$request->get( 'addressType', 'person' ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}

}