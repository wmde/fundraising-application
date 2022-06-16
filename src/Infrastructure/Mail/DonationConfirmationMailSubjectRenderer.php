<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * @license GPL-2.0-or-later
 */
class DonationConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	private TranslatorInterface $translator;
	private string $defaultSubjectKey;
	private string $bankTransferSubjectKey;

	public function __construct( TranslatorInterface $translator, string $defaultSubjectKey, string $bankTransferSubjectKey ) {
		$this->translator = $translator;
		$this->defaultSubjectKey = $defaultSubjectKey;
		$this->bankTransferSubjectKey = $bankTransferSubjectKey;
	}

	public function render( array $templateArguments = [] ): string {
		if ( $templateArguments['donation']['paymentType'] === PaymentType::BankTransfer->value ) {
			return $this->translator->trans( $this->bankTransferSubjectKey );
		}
		return $this->translator->trans( $this->defaultSubjectKey );
	}

}
