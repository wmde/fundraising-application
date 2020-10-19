<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\FallbackRequestValueReader;

class NewDonationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		// TODO Remove LegacyValueReader after January 2021
		$legacyValueReader = new FallbackRequestValueReader( $ffFactory->getLogger(), $request );
		try {
			$amount = Euro::newFromCents( intval( $request->get( 'amount', $legacyValueReader->getAmount() ) ) );
		}
		catch ( \InvalidArgumentException $ex ) {
			$amount = Euro::newFromCents( 0 );
		}
		$paymentType = (string)$request->get( 'paymentType', $legacyValueReader->getPaymentType() );
		$interval = $request->get( 'interval', $legacyValueReader->getInterval() );
		if ( $interval !== null ) {
			$interval = intval( $interval );
		}

		$validationResult = $ffFactory->newPaymentDataValidator()->validate( $amount, $paymentType );

		$trackingInfo = new DonationTrackingInfo();
		$trackingInfo->setTotalImpressionCount( intval( $request->get( 'impCount' ) ) );
		$trackingInfo->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount' ) ) );

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
