<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\MembershipConfirmationMailSubjectRenderer
 */
class MembershipConfirmationMailSubjectRendererTest extends TestCase {

	public function testGivenActiveMembership_activeSubjectLineIsPrinted(): void {
		$templateArguments['membershipType'] = MembershipApplication::ACTIVE_MEMBERSHIP;
		$this->assertSame(
			'mail_subject_confirm_membership_application_active',
			$this->newMembershipConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function testGivenSustainingMembership_sustainingSubjectLineIsPrinted(): void {
		$templateArguments['membershipType'] = MembershipApplication::SUSTAINING_MEMBERSHIP;
		$this->assertSame(
			'mail_subject_confirm_membership_application_sustaining',
			$this->newMembershipConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newMembershipConfirmationMailSubjectRenderer(): MembershipConfirmationMailSubjectRenderer {
		return new MembershipConfirmationMailSubjectRenderer(
			new FakeTranslator(),
			'mail_subject_confirm_membership_application_active',
			'mail_subject_confirm_membership_application_sustaining'
		);
	}
}
