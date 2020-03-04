<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\DeprecatedParamsLogger;

class NewDonationController {

	private function getAmountOrFallback ( Request $request ): int {

		if ( $request->get( 'amount' ) !== null ) {
			return intval( $request->get( 'amount' ) );
		}
		$fallbackParameters = [ 'betrag', 'betrag_auswahl' ];
		foreach ( $fallbackParameters as $fbParam ) {
			if ( $request->get( $fbParam ) !== null ) {
				return intval( $request->get( $fbParam ) ) * 100;
			}
		}
		return 0;
	}

	public function handle( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		DeprecatedParamsLogger::logParamUsage( $ffFactory->getLogger(), $request );

		try {
			$amount = Euro::newFromCents( $this->getAmountOrFallback( $request ) );
		}
		catch ( \InvalidArgumentException $ex ) {
			$amount = Euro::newFromCents( 0 );
		}

		$validationResult = $ffFactory->newPaymentDataValidator()->validate(
			$amount,
			(string)$request->get( 'paymentType', (string)$request->get( 'zahlweise', '' ) )
		);

		$trackingInfo = new DonationTrackingInfo();
		$trackingInfo->setTotalImpressionCount( intval( $request->get( 'impCount' ) ) );
		$trackingInfo->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount' ) ) );

		// TODO: don't we want to use newDonationFormViolationPresenter when !$validationResult->isSuccessful()?

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$request->get( 'paymentType', $request->get( 'zahlweise', '' ) ),
				intval( $request->get( 'interval', $request->get( 'periode', 0 ) ) ),
				$validationResult->isSuccessful(),
				$trackingInfo,
				$request->get( 'addressType', 'person' ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}

}