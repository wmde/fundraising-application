<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

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

	/**
	 * @param AddDonationRequest $request
	 * @return string
	 */
	public function present( AddDonationRequest $request ): string {
		return $this->template->render( [
			'initialFormValues' => [
				'amount' => $this->amountFormatter->format( $request->getAmount() ),
				'paymentType' => $request->getPaymentType(),
				'paymentIntervalInMonths' => $request->getInterval()
			]
		] );
	}

}
