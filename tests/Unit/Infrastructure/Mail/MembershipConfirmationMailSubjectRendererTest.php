<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer
 */
class MembershipConfirmationMailSubjectRendererTest extends \PHPUnit\Framework\TestCase {


	public function testGivenPaypalPayment_defaultSubjectLineIsPrinted() {
		$templateArguments['donation']['paymentType'] = PaymentMethod::PAYPAL;
		$this->assertSame(
			'mail_subject_confirm_donation',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function testGivenBankTransferPayment_bankTransferSubjectLineIsPrinted() {
		$templateArguments['donation']['paymentType'] = PaymentMethod::BANK_TRANSFER;
		$this->assertSame(
			'mail_subject_confirm_donation_promise',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newDonationConfirmationMailSubjectRenderer(): DonationConfirmationMailSubjectRenderer {
		return new DonationConfirmationMailSubjectRenderer(
			new Translator( 'zz_ZZ' ),
			'mail_subject_confirm_donation',
			'mail_subject_confirm_donation_promise'
		);
	}
}
