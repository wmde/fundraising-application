<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipMailerAdapter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\Notification\ApplyForMembershipTemplateArguments;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipMailerAdapter
 */
class MembershipMailerAdapterTest extends TestCase {
	public function testAdapterConvertsObjectPropertiesToArray(): void {
		$mailerSpy = new TemplateBasedMailerSpy( $this );
		$adapter = new MembershipMailerAdapter( $mailerSpy );
		$emailAddress = new EmailAddress( 'musterfrau@beispiel.de' );
		$templateDTO = new ApplyForMembershipTemplateArguments(
			id: 1,
			membershipType: 'sustaining',
			membershipFee: '12.00',
			membershipFeeInCents: 1200,
			paymentIntervalInMonths: 1,
			paymentType: 'BEZ',
			salutation: 'Frau',
			title: 'Dr.',
			lastName: 'Musterfrau',
			firstName: 'Annika',
			hasReceiptEnabled: true,
			incentives: [],
			moderationFlags: []
		);

		$adapter->sendMail( $emailAddress, $templateDTO );

		$mailerSpy->assertCalledOnceWith( $emailAddress, [
			'id' => 1,
			'membershipType' => 'sustaining',
			'membershipFee' => '12.00',
			'membershipFeeInCents' => 1200,
			'paymentIntervalInMonths' => 1,
			'paymentType' => 'BEZ',
			'salutation' => 'Frau',
			'title' => 'Dr.',
			'lastName' => 'Musterfrau',
			'firstName' => 'Annika',
			'hasReceiptEnabled' => true,
			'incentives' => [],
			'moderationFlags' => []
		] );
	}
}
