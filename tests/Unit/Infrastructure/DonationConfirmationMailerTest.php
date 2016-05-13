<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;

class DonationConfirmationMailerTest extends \PHPUnit_Framework_TestCase {

	public function testMailerExtractsEmailFromDonation() {
		$donation = ValidDonation::newBankTransferDonation();
		$templateMailer = $this->getMockBuilder( TemplateBasedMailer::class )->disableOriginalConstructor()->getMock();
		$templateMailer->expects( $this->once() )
			->method( 'sendMail' )
			->with(
				$this->equalTo( ValidDonation::DONOR_EMAIL_ADDRESS ),
				$this->anything()
			);
		$mailer = new DonationConfirmationMailer( $templateMailer );
		$mailer->sendConfirmationMailFor( $donation );
	}

	public function testMailerAssemblesTemplateData() {
		$donation = ValidDonation::newBankTransferDonation();
		$expectedTemplateData = [
			'recipient' => [
				'lastName' => ValidDonation::DONOR_LAST_NAME,
				'title' => ValidDonation::DONOR_TITLE,
				'salutation' => ValidDonation::DONOR_SALUTATION
			],
			'donation' => [
				'id' => $donation->getId(),
				'amount' => ValidDonation::DONATION_AMOUNT,
				'interval' => ValidDonation::PAYMENT_INTERVAL_IN_MONTHS,
				'needsModeration' => false,
				'paymentType' => PaymentType::BANK_TRANSFER,
				'bankTransferCode' => ValidDonation::PAYMENT_BANK_TRANSFER_CODE
			]
		];
		$templateMailer = $this->getMockBuilder( TemplateBasedMailer::class )->disableOriginalConstructor()->getMock();
		$templateMailer->expects( $this->once() )
			->method( 'sendMail' )
			->with(
				$this->anything(),
				$this->equalTo( $expectedTemplateData )
			);
		$mailer = new DonationConfirmationMailer( $templateMailer );
		$mailer->sendConfirmationMailFor( $donation );
	}

}
