<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationResponse;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\CreditCard;

/**
 * @licence GNU GPL v2+
 */
class CreditCardPaymentUrlGenerator {

	private TranslatorInterface $translator;
	private CreditCard $urlGenerator;

	public function __construct( TranslatorInterface $translator, CreditCard $urlGenerator ) {
		$this->translator = $translator;
		$this->urlGenerator = $urlGenerator;
	}

	public function buildUrl( AddDonationResponse $response ): string {
		$personalInfo = $response->getDonation()->getDonor();

		return $this->urlGenerator->generateUrl(
			$personalInfo ? $personalInfo->getName()->getFirstName() : '',
			$personalInfo ? $personalInfo->getName()->getLastName() : '',
			$this->translator->trans(
				'credit_card_item_name_donation',
				[
					'%interval%' =>
						$this->translator->trans(
							'payment_interval_' . $response->getDonation()->getPaymentIntervalInMonths()
						)
				]
			),
			$response->getDonation()->getId(),
			$response->getAccessToken(),
			$response->getUpdateToken(),
			$response->getDonation()->getAmount()
		);
	}

}