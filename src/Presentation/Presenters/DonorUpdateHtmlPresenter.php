<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorResponse;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\PaymentContext\UseCases\GetPayment\GetPaymentUseCase;

/**
 * Render the confirmation pages for donations with additional form to enter / update donation data
 */
class DonorUpdateHtmlPresenter {

	public function __construct(
		private readonly TwigTemplate $template,
		private readonly UrlGenerator $urlGenerator,
		private readonly GetPaymentUseCase $getPaymentUseCase ) {
	}

	public function present( UpdateDonorResponse $updateDonorResponse, Donation $donation, string $updateToken, string $accessToken ): string {
		return $this->template->render(
			array_merge(
				$this->getConfirmationPageArguments( $donation, $updateToken, $accessToken ),
				[
					'updateData' => [
						'isSuccessful' => $updateDonorResponse->isSuccessful(),
						'message' => !empty( $updateDonorResponse->getSuccessMessage() ) ?
							$updateDonorResponse->getSuccessMessage() : $updateDonorResponse->getErrorMessage()
					]
				]
			)
		);
	}

	private function getConfirmationPageArguments( Donation $donation, string $updateToken, string $accessToken ): array {
		$donorDataFormatter = new DonorDataFormatter();
		$paymentData = $this->getPaymentUseCase->getPaymentDataArray( $donation->getPaymentId() );
		return [
			'donation' => [
				'id' => $donation->getId(),
				// TODO: Adapt the front end to take cents here for currency localisation
				'amount' => Euro::newFromCents( $paymentData['amount'] )->getEuroFloat(),
				'amountInCents' => $paymentData['amount'],
				'interval' => $paymentData['interval'],
				'paymentType' => $paymentData['paymentType'],
				'optsIntoDonationReceipt' => $donation->getOptsIntoDonationReceipt(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $paymentData['paymentReferenceCode'] ?? '',
				'creationDate' => $donorDataFormatter->getDonationDate(),
				'cookieDuration' => $donorDataFormatter->getHideBannerCookieDuration(),
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'address' => $donorDataFormatter->getAddressArguments( $donation ),
			'bankData' => BankDataPresenter::getBankDataArray( $paymentData ),
			'commentUrl' => $this->urlGenerator->generateRelativeUrl(
				'AddCommentPage',
				[
					'donationId' => $donation->getId(),
					'updateToken' => $updateToken,
					'accessToken' => $accessToken
				]
			)
		];
	}
}
