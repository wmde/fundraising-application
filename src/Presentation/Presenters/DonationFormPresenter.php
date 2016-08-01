<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationFormPresenter {

	private $template;
	private $amountFormatter;

	public function __construct( TwigTemplate $template, AmountFormatter $amountFormatter ) {
		$this->template = $template;
		$this->amountFormatter = $amountFormatter;
	}

	public function present( Euro $amount, string $paymentType, int $paymentInterval ): string {
		return $this->template->render( [
			'initialFormValues' => [
				'amount' => $this->amountFormatter->format( $amount ),
				'paymentType' => $paymentType,
				'paymentIntervalInMonths' => $paymentInterval
			]
		] );
	}

}
