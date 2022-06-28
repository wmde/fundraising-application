<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;

/**
 * @license GPL-2.0-or-later
 */
class DonationFormPresenter {

	private TwigTemplate $template;
	private IsCustomAmountValidator $isCustomDonationAmountValidator;

	public function __construct(
		TwigTemplate $template,
		IsCustomAmountValidator $isCustomDonationAmountValidator
	) {
		$this->template = $template;
		$this->isCustomDonationAmountValidator = $isCustomDonationAmountValidator;
	}

	public function present( int $amount, string $paymentType, ?int $paymentInterval, bool $paymentDataIsValid,
							 ImpressionCounts $trackingInfo, ?string $addressType, array $urlEndpoints ): string {
		try {
			$euroAmount = Euro::newFromCents( $amount );
		} catch ( \InvalidArgumentException $ex ) {
			$euroAmount = Euro::newFromCents( 0 );
		}
		return $this->template->render( [
			'initialFormValues' => [
				'amount' => $euroAmount->getEuroCents(),
				'paymentType' => $paymentType,
				'paymentIntervalInMonths' => $paymentInterval,
				'isCustomAmount' => $this->isCustomDonationAmountValidator->validate( $euroAmount ),
				'addressType' => $addressType
			],
			'validationResult' => [
				'paymentData' => $paymentDataIsValid
			],
			'tracking' => [
				'bannerImpressionCount' => $trackingInfo->getSingleBannerImpressionCount(),
				'impressionCount' => $trackingInfo->getTotalImpressionCount()
			],
			'urls' => $urlEndpoints
		] );
	}

}
