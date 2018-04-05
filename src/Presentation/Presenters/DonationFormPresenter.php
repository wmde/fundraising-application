<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationFormPresenter {

	private $template;
	private $amountFormatter;
	private $isCustomDonationAmountValidator;

	public function __construct(
		TwigTemplate $template,
		AmountFormatter $amountFormatter,
		IsCustomAmountValidator $isCustomDonationAmountValidator
	) {
		$this->template = $template;
		$this->amountFormatter = $amountFormatter;
		$this->isCustomDonationAmountValidator = $isCustomDonationAmountValidator;
	}

	public function present( Euro $amount, string $paymentType, int $paymentInterval, bool $paymentDataIsValid,
							 DonationTrackingInfo $trackingInfo, string $addressType ): string {
		return $this->template->render( [
			'initialFormValues' => [
				'amount' => $this->amountFormatter->format( $amount ),
				'paymentType' => $paymentType,
				'paymentIntervalInMonths' => $paymentInterval,
				'isCustomAmount' => $this->isCustomDonationAmountValidator->validate( $amount ),
				'addressType' => $addressType
			],
			'validationResult' => [
				'paymentData' => $paymentDataIsValid
			],
			'tracking' => [
				'bannerImpressionCount' => $trackingInfo->getSingleBannerImpressionCount(),
				'impressionCount' => $trackingInfo->getTotalImpressionCount()
			]
		] );
	}

}
