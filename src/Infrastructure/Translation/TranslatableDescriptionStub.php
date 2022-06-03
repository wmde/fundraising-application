<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\RefactoringException;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\TranslatableDescription;

// TODO add constructor with translation key, translations and locale
//     locale could also be setter-injected, since it's determined at runtime and may not be correct at construction time
class TranslatableDescriptionStub implements TranslatableDescription {

	public function __construct(
		private $localisationString
	) {
	}

	public function getText( Euro $paymentAmount, PaymentInterval $paymentInterval ): string {
		// TODO: do translation with translation key, locale, messages. Replace placeholders in message, giving it interval and amount.
		throw new RefactoringException( "This class needs a proper implementation ({$this->localisationString})" );
	}
}
