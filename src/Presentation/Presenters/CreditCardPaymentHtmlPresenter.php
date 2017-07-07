<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationResponse;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\CreditCard as CreditCardUrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the credit card payment page embedding an iframe
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardPaymentHtmlPresenter {

	private $template;
	/** @var Translator $translator */
	private $translator;
	private $urlGenerator;

	public function __construct( TwigTemplate $template, TranslatorInterface $translator,
								CreditCardUrlGenerator $urlGenerator ) {
		$this->template = $template;
		$this->translator = $translator;
		$this->urlGenerator = $urlGenerator;
	}

	public function present( AddDonationResponse $response ): string {
		return $this->template->render( $this->getArguments( $response ) );
	}

	private function getArguments( AddDonationResponse $response ) {
		$personalInfo = $response->getDonation()->getDonor();
		return [
			'donation' => [
				'interval' => $response->getDonation()->getPaymentIntervalInMonths(),
				'amount' => $response->getDonation()->getAmount()->getEuroString()
			],
			'iframeUrl' => $this->urlGenerator->generateUrl(
				$personalInfo ? $personalInfo->getName()->getFirstName() : '',
				$personalInfo ? $personalInfo->getName()->getLastName() : '',
				$this->translator->trans( 'paytext_cc', [], 'paymentIntervals' ) . ' ' .
					$this->translator->trans( $response->getDonation()->getPaymentIntervalInMonths(), [], 'paymentIntervals' ),
				$response->getDonation()->getId(),
				$response->getAccessToken(),
				$response->getUpdateToken(),
				$response->getDonation()->getAmount()
			),
		];
	}

}