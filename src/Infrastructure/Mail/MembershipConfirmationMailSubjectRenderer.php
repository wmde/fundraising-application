<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;

class MembershipConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	private string $activeMembershipSubject;

	public function __construct(
		private readonly TranslatorInterface $translator,
		string $activeMembershipSubject,
		private readonly string $sustainingMembershipSubject
	) {
		$this->activeMembershipSubject = $activeMembershipSubject;
	}

	public function render( array $templateArguments = [] ): string {
		if ( $templateArguments['membershipType'] === MembershipApplication::ACTIVE_MEMBERSHIP ) {
			return $this->translator->trans( $this->activeMembershipSubject );
		}
		return $this->translator->trans( $this->sustainingMembershipSubject );
	}

}
