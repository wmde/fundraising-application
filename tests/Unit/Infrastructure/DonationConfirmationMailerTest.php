<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationConfirmationMailer
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationConfirmationMailerTest extends \PHPUnit_Framework_TestCase {

	const DONATION_ID = 42;

	public function testMailerExtractsEmailFromDonation() {
		$mailer = new TemplateBasedMailerSpy( $this );

		$donationMailer = new \WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationConfirmationMailer( $mailer );
		$donationMailer->sendConfirmationMailFor( $this->newDonation() );

		$mailer->assertCalledOnce();
		$this->assertEquals(
			new EmailAddress( ValidDonation::DONOR_EMAIL_ADDRESS ),
			$mailer->getSendMailCalls()[0][0]
		);
	}

	private function newDonation(): Donation {
		$donation = ValidDonation::newBankTransferDonation();
		$donation->assignId( self::DONATION_ID );
		return $donation;
	}

	public function testMailerAssemblesTemplateData() {
		$mailer = new TemplateBasedMailerSpy( $this );

		$donationMailer = new DonationConfirmationMailer( $mailer );
		$donationMailer->sendConfirmationMailFor( $this->newDonation() );

		$mailer->assertCalledOnce();
		$this->assertEquals(
			[
				'recipient' => [
					'lastName' => ValidDonation::DONOR_LAST_NAME,
					'title' => ValidDonation::DONOR_TITLE,
					'salutation' => ValidDonation::DONOR_SALUTATION
				],
				'donation' => [
					'id' => self::DONATION_ID,
					'amount' => ValidDonation::DONATION_AMOUNT,
					'interval' => ValidDonation::PAYMENT_INTERVAL_IN_MONTHS,
					'needsModeration' => false,
					'paymentType' => PaymentType::BANK_TRANSFER,
					'bankTransferCode' => ValidDonation::PAYMENT_BANK_TRANSFER_CODE
				]
			],
			$mailer->getSendMailCalls()[0][1]
		);
	}

}
