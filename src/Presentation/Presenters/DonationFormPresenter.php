<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

class DonationFormPresenter {

	public function __construct(
		private readonly TwigTemplate $template,
		private readonly IsCustomAmountValidator $isCustomDonationAmountValidator
	) {
	}

	/**
	 * @param int $amount
	 * @param string $paymentType
	 * @param int|null $paymentInterval
	 * @param bool|null $receipt
	 * @param ValidationResponse $paymentValidationResult
	 * @param ImpressionCounts $trackingInfo
	 * @param string|null $addressType
	 * @param array<string, string> $urlEndpoints
	 */
	public function present( int $amount, string $paymentType, ?int $paymentInterval, ?bool $receipt, ValidationResponse $paymentValidationResult,
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
				'addressType' => $addressType,
				'receipt' => $receipt
			],
			'validationResult' => [
				'paymentErrorFields' => array_unique( array_map(
					static fn ( ConstraintViolation $violation ) => $violation->getSource(),
					$paymentValidationResult->getValidationErrors()
				) ),
				// deprecated, remove when frontend uses the 'paymentErrorFields' array
				'paymentData' => $paymentValidationResult->isSuccessful()
			],
			'tracking' => [
				'bannerImpressionCount' => $trackingInfo->getSingleBannerImpressionCount(),
				'impressionCount' => $trackingInfo->getTotalImpressionCount()
			],
			'urls' => $urlEndpoints
		] );
	}

}
