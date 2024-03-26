<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationNotifier\TemplateArgumentsAdmin;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\AdminDonationModerationMailerAdapter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\AdminDonationModerationMailerAdapter
 */
class AdminDonationModerationMailerAdapterTest extends TestCase {
	public function testAdapterConvertsObjectPropertiesToArray(): void {
		$mailerSpy = new TemplateBasedMailerSpy( $this );
		$mailer = new AdminDonationModerationMailerAdapter( $mailerSpy );

		$mailer->sendMail(
			new EmailAddress( 'prankster@example.com' ),
			new TemplateArgumentsAdmin( 1, [ 'AMOUNT_TOO_HIGH' => true ], 1000000000 )
		);

		$mailerSpy->assertCalledOnceWith(
			new EmailAddress( 'prankster@example.com' ),
			[
				'id' => 1,
				'moderationFlags' => [ 'AMOUNT_TOO_HIGH' => true ],
				'amount' => 1000000000
			]
		);
	}
}
