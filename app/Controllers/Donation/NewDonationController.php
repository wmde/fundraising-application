<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\RequestSearcher;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

class NewDonationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$ffFactory->getTranslationCollector()->addTranslationFile(
			$ffFactory->getI18nDirectory() . '/messages/paymentTypes.json'
		);

		$amount = intval( RequestSearcher::get( $request, 'amount', 0 ) );
		$paymentType = (string)RequestSearcher::get( $request, 'paymentType', '' );
		$interval = $this->getIntervalFromRequest( $request );
		$receipt = $this->getReceiptFromRequest( $request );

		$validationResult = $ffFactory->newPaymentValidator()->validatePaymentData(
			$amount,
				$interval ?? PaymentInterval::OneTime->value,
			$paymentType,
			$ffFactory->newDonationPaymentValidator()
		);

		$trackingInfo = new ImpressionCounts(
			intval( RequestSearcher::get( $request, 'impCount' ) ),
			intval( RequestSearcher::get( $request, 'bImpCount' ) )
		);

		return new Response(
			$ffFactory->newDonationFormPresenter()->present(
				$amount,
				$paymentType,
				$interval,
				$receipt,
				$validationResult,
				$trackingInfo,
				RequestSearcher::get( $request, 'addressType' ),
				Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() )
			)
		);
	}

	private function getIntervalFromRequest( Request $request ): ?int {
		$interval = RequestSearcher::get( $request, 'interval', null );

		if ( is_string( $interval ) ) {
			return intval( $interval );
		}

		return $interval;
	}

	private function getReceiptFromRequest( Request $request ): ?bool {
		$receipt = RequestSearcher::get( $request, 'receipt' );

		if ( $receipt === 'true' ) {
			return true;
		}

		if ( $receipt === 'false' ) {
			return false;
		}

		return null;
	}
}
