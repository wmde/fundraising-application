<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @license GNU GPL v2+
 */
class DonationConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	private $translator;
	private $defaultSubjectKey;
	private $bankTransferSubjectKey;

	public function __construct( TranslatorInterface $translator, string $defaultSubjectKey, string $bankTransferSubjectKey ) {
		$this->translator = $translator;
		$this->defaultSubjectKey = $defaultSubjectKey;
		$this->bankTransferSubjectKey = $bankTransferSubjectKey;
	}

	public function render( array $templateArguments = [] ): string {
		if ( $templateArguments['donation']['paymentType'] === PaymentMethod::BANK_TRANSFER ) {
			return $this->translator->trans( $this->bankTransferSubjectKey );
		}
		return $this->translator->trans( $this->defaultSubjectKey );
	}

}
