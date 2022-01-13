<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations
 *
 * @license GPL-2.0-or-later
 */
class DonationConfirmationHtmlPresenter {

	private TwigTemplate $template;
	private UrlGenerator $urlGenerator;
	private array $countries;
	private object $validation;

	public function __construct( TwigTemplate $template, UrlGenerator $urlGenerator, array $countries, object $validation ) {
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
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
		$donorDataFormatter = new DonorDataFormatter();
		return [
			'donation' => [
				'id' => $donation->getId(),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentMethodId(),
				'optsIntoDonationReceipt' => $donation->getOptsIntoDonationReceipt(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $donorDataFormatter->getBankTransferCode( $donation->getPaymentMethod() ),
				'creationDate' => $donorDataFormatter->getDonationDate(),
				'cookieDuration' => $donorDataFormatter->getHideBannerCookieDuration(),
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'countries' => $this->countries,
			'addressValidationPatterns' => $this->validation,
			'addressType' => AddressType::donorToPresentationAddressType( $donation->getDonor() ),
			'address' => $donorDataFormatter->getAddressArguments( $donation ),
			'bankData' => $donorDataFormatter->getBankDataArguments( $donation->getPaymentMethod() ),
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
