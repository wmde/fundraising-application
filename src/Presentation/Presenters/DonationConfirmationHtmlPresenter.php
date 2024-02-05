<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Authentication\DonationUrlAuthenticationLoader;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations
 */
class DonationConfirmationHtmlPresenter {

	private object $addressValidationPatterns;

	public function __construct(
		private readonly TwigTemplate $template,
		private readonly UrlGenerator $urlGenerator,
		private readonly DonationUrlAuthenticationLoader $authenticationLoader,
		private readonly array $countries,
		object $validation
	) {
		$this->addressValidationPatterns = $validation;
	}

	public function present( Donation $donation, array $paymentData, array $urlEndpoints ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments( $donation, $paymentData, $urlEndpoints )
		);
	}

	private function getConfirmationPageArguments( Donation $donation, array $paymentData, array $urlEndpoints ): array {
		$donorDataFormatter = new DonorDataFormatter();

		$donationParameters = [
			'id' => $donation->getId(),
			// TODO: Adapt the front end to take cents here for currency localisation
			'amount' => Euro::newFromCents( $paymentData['amount'] )->getEuroFloat(),
			'amountInCents' => $paymentData['amount'],
			'interval' => $paymentData['interval'],
			'paymentType' => $paymentData['paymentType'],
			'receipt' => $donation->getDonor()->wantsReceipt(),
			'newsletter' => $donation->getDonor()->isSubscribedToMailingList(),
			'mailingList' => $donation->getDonor()->isSubscribedToMailingList(),
			'bankTransferCode' => $paymentData['paymentReferenceCode'] ?? '',
			'creationDate' => $donorDataFormatter->getDonationDate(),
			'cookieDuration' => $donorDataFormatter->getHideBannerCookieDuration(),
			'isExported' => $donation->isExported(),
		];
		$donationParameters = $this->authenticationLoader->addDonationAuthorizationParameters( $donation->getId(), $donationParameters );
		return [
			'donation' => $donationParameters,
			'countries' => $this->countries,
			'addressValidationPatterns' => $this->addressValidationPatterns,
			'addressType' => AddressType::donorToPresentationAddressType( $donation->getDonor() ),
			'address' => $donorDataFormatter->getAddressArguments( $donation ),
			'tracking' => $donation->getTrackingInfo()->getTracking(),
			'bankData' => [
				'iban' => $paymentData['iban'] ?? '',
				'bic' => $paymentData['bic'] ?? '',
				'bankname' => $paymentData['bankname'] ?? '',
			],
			'urls' => [
				...$urlEndpoints,
				'addComment'  => $this->createUrlForAddingComment( $donation->getId() ),
			]
		];
	}

	private function createUrlForAddingComment( int $donationId ): string {
		return $this->urlGenerator->generateRelativeUrl(
			'AddCommentPage',
			$this->authenticationLoader->addDonationAuthorizationParameters(
				$donationId,
				[
					'donationId' => $donationId,
				]
			)
		);
	}
}
