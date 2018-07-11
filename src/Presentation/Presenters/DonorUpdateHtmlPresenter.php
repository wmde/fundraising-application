<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorResponse;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations with additional form to enter / update donation data
 *
 * @license GNU GPL v2+
 */
class DonorUpdateHtmlPresenter {

	private $template;
	private $donationMembershipApplicationAdapter;
	private $urlGenerator;
	private $donorDataFormatter;

	public function __construct( TwigTemplate $template, UrlGenerator $urlGenerator ) {
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
		$this->donationMembershipApplicationAdapter = new DonationMembershipApplicationAdapter();
		$this->donorDataFormatter = new DonorDataFormatter();
	}

	public function present( UpdateDonorResponse $updateDonorResponse, Donation $donation, string $updateToken, string $accessToken ): string {
		return $this->template->render(
			array_merge(
				$this->getConfirmationPageArguments( $updateDonorResponse, $donation, $updateToken, $accessToken ),
				[
					'updateData' => [
						'isSuccessful' => $updateDonorResponse->isSuccessful(),
						'message' => !empty( $updateDonorResponse->getSuccessMessage() ) ?
							$updateDonorResponse->getSuccessMessage() : $updateDonorResponse->getErrorMessage()
					] ]
			)
		);
	}

	private function getConfirmationPageArguments( UpdateDonorResponse $updateDonorResponse, Donation $donation, string $updateToken, string $accessToken ): array {
		return [
			'donation' => [
				'id' => $donation->getId(),
				'status' => $this->donorDataFormatter->mapStatus( $donation->getStatus() ),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentMethodId(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $this->donorDataFormatter->getBankTransferCode(
					$donation->getPaymentMethod()
				),
				'creationDate' => $this->donorDataFormatter->getDonationDate(),
				'cookieDuration' => $this->donorDataFormatter->getHideBannerCookieDuration(),
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'address' => $this->donorDataFormatter->getAddressArguments( $donation ),
			'bankData' => $this->donorDataFormatter->getBankDataArguments( $donation->getPaymentMethod() ),
			'piwikEvents' => [],
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
