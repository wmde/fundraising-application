<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

class DonationConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	public function __construct(
		private readonly TranslatorInterface $translator,
		private readonly string $defaultSubjectKey,
		private readonly string $bankTransferSubjectKey
	) {
	}

	/**
	 * @param array<string, mixed> $templateArguments
	 */
	public function render( array $templateArguments = [] ): string {
		if (
			is_array( $templateArguments['donation'] ) &&
			isset( $templateArguments['donation']['paymentType'] ) &&
			$templateArguments['donation']['paymentType'] === PaymentType::BankTransfer->value
		) {
				return $this->translator->trans( $this->bankTransferSubjectKey );
		}
		return $this->translator->trans( $this->defaultSubjectKey );
	}

}
