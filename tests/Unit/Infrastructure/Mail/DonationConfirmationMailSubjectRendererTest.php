<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\DonationConfirmationMailSubjectRenderer
 */
class DonationConfirmationMailSubjectRendererTest extends \PHPUnit\Framework\TestCase {


	public function testGivenActiveMembership_activeSubjectLineIsPrinted() {
		$templateArguments['membershipType'] = Application::ACTIVE_MEMBERSHIP;
		$this->assertSame(
			'mail_subject_confirm_membership_application_active',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function testGivenSustainingMembership_sustainingSubjectLineIsPrinted() {
		$templateArguments['membershipType'] = Application::SUSTAINING_MEMBERSHIP;
		$this->assertSame(
			'mail_subject_confirm_membership_application_sustaining',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newDonationConfirmationMailSubjectRenderer(): MembershipConfirmationMailSubjectRenderer {
		return new MembershipConfirmationMailSubjectRenderer(
			new Translator( 'zz_ZZ' ),
			'mail_subject_confirm_membership_application_active',
			'mail_subject_confirm_membership_application_sustaining'
		);
	}
}
