<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorResponse;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations with additional form to enter / update donation data
 *
 * @license GPL-2.0-or-later
 */
class DonorUpdateHtmlPresenter {

	private TwigTemplate $template;
	private UrlGenerator $urlGenerator;

	public function __construct( TwigTemplate $template, UrlGenerator $urlGenerator ) {
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
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
		return [
			'donation' => [
				'id' => $donation->getId(),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentMethodId(),
				'optsIntoDonationReceipt' => $donation->getOptsIntoDonationReceipt(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $donorDataFormatter->getBankTransferCode(
					$donation->getPaymentMethod()
				),
				'creationDate' => $donorDataFormatter->getDonationDate(),
				'cookieDuration' => $donorDataFormatter->getHideBannerCookieDuration(),
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'address' => $donorDataFormatter->getAddressArguments( $donation ),
			'bankData' => $donorDataFormatter->getBankDataArguments( $donation->getPaymentMethod() ),
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
