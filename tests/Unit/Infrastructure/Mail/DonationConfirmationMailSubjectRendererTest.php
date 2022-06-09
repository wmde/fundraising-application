<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer
 */
class DonationConfirmationMailSubjectRendererTest extends TestCase {

	public function testGivenPaypalPayment_defaultSubjectLineIsPrinted() {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$templateArguments['donation']['paymentType'] = PaymentMethod::PAYPAL;
		$this->assertSame(
			'mail_subject_confirm_donation',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function testGivenBankTransferPayment_bankTransferSubjectLineIsPrinted() {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$templateArguments['donation']['paymentType'] = PaymentMethod::BANK_TRANSFER;
		$this->assertSame(
			'mail_subject_confirm_donation_promise',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newDonationConfirmationMailSubjectRenderer(): DonationConfirmationMailSubjectRenderer {
		return new DonationConfirmationMailSubjectRenderer(
			new FakeTranslator(),
			'mail_subject_confirm_donation',
			'mail_subject_confirm_donation_promise'
		);
	}
}
