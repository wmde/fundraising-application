<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationNotifier\TemplateArgumentsDonation;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationNotifier\TemplateArgumentsDonationConfirmation;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailerAdapter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

#[CoversClass( DonationConfirmationMailerAdapter::class )]
class DonationConfirmationMailerAdapterTest extends TestCase {
	public function testAdapterConvertsObjectPropertiesToArray(): void {
		$donation = new TemplateArgumentsDonation(
			id: 1,
			amount: 12.99,
			amountInCents: 1299,
			interval: 1,
			paymentType: 'UEB',
			needsModeration: false,
			moderationFlags: [ 'SOME_REASON' => true ],
			bankTransferCode: 'XW-93C',
			receiptOptIn: true
		);
		$confirmation = new TemplateArgumentsDonationConfirmation(
			[ 'companyName' => 'ACME Finest Products' ],
			$donation
		);
		$mailerSpy = new TemplateBasedMailerSpy( $this );
		$mailer = new DonationConfirmationMailerAdapter( $mailerSpy );

		$mailer->sendMail( new EmailAddress( 'donorCompany@example.com' ), $confirmation );

		$mailerSpy->assertCalledOnceWith(
			new EmailAddress( 'donorCompany@example.com' ),
			[
				'recipient' => [ 'companyName' => 'ACME Finest Products' ],
				'donation' => [
					'id' => 1,
					'amount' => 12.99,
					'amountInCents' => 1299,
					'interval' => 1,
					'paymentType' => 'UEB',
					'needsModeration' => false,
					'moderationFlags' => [ 'SOME_REASON' => true ],
					'bankTransferCode' => 'XW-93C',
					'receiptOptIn' => true
				]
			]
		);
	}

}
