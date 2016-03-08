<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Domain\CreditCardUrlGenerator;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
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

	public function present( Donation $donation ): string {
		return $this->template->render( $this->getArguments( $donation ) );
	}

	private function getArguments( Donation $donation ) {
		return [
			'iframeUrl' => $this->urlGenerator->generateUrl(
				$donation->getPersonalInfo() ? $donation->getPersonalInfo()->getPersonName()->getFirstName() : '',
				$donation->getPersonalInfo() ? $donation->getPersonalInfo()->getPersonName()->getLastName() : '',
				$this->translator->trans( 'paytext_cc', [], 'paymentIntervals' ) . ' ' .
				$this->translator->trans( $donation->getInterval(), [], 'paymentIntervals' ),
				$donation->getId(),
				// TODO: get update token
				'update_token',
				$donation->getAmount()
			),
		];
	}

}