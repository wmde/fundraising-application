<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

class NewDonationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		$amount = intval( $request->get( 'amount', 0 ) );
		$paymentType = (string)$request->get( 'paymentType', '' );
		$interval = $this->getIntervalFromRequest( $request );

		$validationResult = $ffFactory->newPaymentValidator()->validatePaymentData(
			$amount,
				$interval ?? PaymentInterval::OneTime->value,
			$paymentType,
			$ffFactory->newDonationPaymentValidator()
		);

		$trackingInfo = new ImpressionCounts(
			intval( $request->get( 'impCount' ) ),
			intval( $request->get( 'bImpCount' ) )
		);

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$paymentType,
				$interval,
				$validationResult,
				$trackingInfo,
				$request->get( 'addressType' ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}

	private function getIntervalFromRequest( Request $request ): ?int {
		$interval = $request->get( 'interval', null );

		if ( is_string( $interval ) ) {
			return intval( $interval );
		}

		return $interval;
	}
}
