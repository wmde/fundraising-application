<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\TranslatableDescription;

class TranslatablePaymentItemDescription implements TranslatableDescription {

	public function __construct(
		private readonly string $localisationString,
		private readonly TranslatorInterface $translatorInterface
	) {
	}

	public function getText( Euro $paymentAmount, PaymentInterval $paymentInterval ): string {
		return $this->translatorInterface->trans(
			$this->localisationString,
			[
				'%amount%' => $paymentAmount->getEuroFloat(),
				'%interval%' => $this->getTranslatedInterval( $paymentInterval->value )
			]
		);
	}

	private function getTranslatedInterval( int $value ): string {
		// See i18n repository, messages/paymentIntervals.json
		$messageKey = "payment_interval_$value";
		return $this->translatorInterface->trans( $messageKey );
	}
}
