<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations
 *
 * @licence GNU GPL v2+
 */
class DonationConfirmationHtmlPresenter {

	private TwigTemplate $template;
	private DonationMembershipApplicationAdapter $donationMembershipApplicationAdapter;
	private UrlGenerator $urlGenerator;
	private DonorDataFormatter $donorDataFormatter;
	private array $countries;
	private object $validation;

	public function __construct( TwigTemplate $template, UrlGenerator $urlGenerator, array $countries, object $validation ) {
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
		$this->donationMembershipApplicationAdapter = new DonationMembershipApplicationAdapter();
		$this->donorDataFormatter = new DonorDataFormatter();
		$this->countries = $countries;
		$this->validation = $validation;
	}

	public function present( Donation $donation, string $updateToken, string $accessToken,
							 array $urlEndpoints ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments( $donation, $updateToken, $accessToken, $urlEndpoints )
		);
	}

	private function getConfirmationPageArguments( Donation $donation, string $updateToken, string $accessToken,
		array $urlEndpoints ): array {

		return [
			'donation' => [
				'id' => $donation->getId(),
				'status' => $this->donorDataFormatter->mapStatus( $donation->getStatus() ),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentMethodId(),
				'optsIntoDonationReceipt' => $donation->getOptsIntoDonationReceipt(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $this->donorDataFormatter->getBankTransferCode( $donation->getPaymentMethod() ),
				'creationDate' => $this->donorDataFormatter->getDonationDate(),
				'cookieDuration' => $this->donorDataFormatter->getHideBannerCookieDuration(),
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'countries' => $this->countries,
			'addressValidationPatterns' => $this->validation,
			'addressType' => $this->donorDataFormatter->getAddressType( $donation ),
			'address' => $this->donorDataFormatter->getAddressArguments( $donation ),
			'bankData' => $this->donorDataFormatter->getBankDataArguments( $donation->getPaymentMethod() ),
			'urls' => array_merge(
				$urlEndpoints,
				[
					'addComment'  => $this->urlGenerator->generateRelativeUrl(
						'AddCommentPage',
						[
							'donationId' => $donation->getId(),
							'updateToken' => $updateToken,
							'accessToken' => $accessToken
						]
					)
				]
			)
		];
	}
}
