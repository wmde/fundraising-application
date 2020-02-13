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
use WMDE\Fundraising\Frontend\Infrastructure\AmountParser;

class NewDonationController {

	public function handle( FunFunFactory $ffFactory, Request $request, Application $app ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);
		$app['session']->set(
			'piwikTracking',
			array_filter(
				[
					'paymentType' => $request->get( 'zahlweise', '' ),
					'paymentAmount' => $request->get( 'betrag', '' ),
					'paymentInterval' => $request->get( 'periode', '' )
				],
				function ( string $value ) {
					return $value !== '' && strlen( $value ) < 20;
				}
			)
		);

		try {
			$amount = Euro::newFromFloat(
				( new AmountParser( 'en_EN' ) )->parseAsFloat(
					$request->get( 'betrag_auswahl', $request->get( 'betrag',  $request->get( 'amountGiven', '' ) ) )
				)
			);
		}
		catch ( \InvalidArgumentException $ex ) {
			$amount = Euro::newFromCents( 0 );
		}
		$validationResult = $ffFactory->newPaymentDataValidator()->validate(
			$amount,
			(string)$request->get( 'zahlweise', '' )
		);

		$trackingInfo = new DonationTrackingInfo();
		$trackingInfo->setTotalImpressionCount( intval( $request->get( 'impCount' ) ) );
		$trackingInfo->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount' ) ) );

		// TODO: don't we want to use newDonationFormViolationPresenter when !$validationResult->isSuccessful()?

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$request->get( 'zahlweise', '' ),
				intval( $request->get( 'periode', 0 ) ),
				$validationResult->isSuccessful(),
				$trackingInfo,
				$request->get( 'addressType', 'person' ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}

}