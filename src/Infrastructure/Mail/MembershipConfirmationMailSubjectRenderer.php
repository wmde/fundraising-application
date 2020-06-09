<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;

/**
 * @license GNU GPL v2+
 */
class MembershipConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	private TranslatorInterface $translator;
	private string $activeMembershipSubject;
	private string $sustainingMembershipSubject;

	public function __construct( TranslatorInterface $translator, string $activeMembershipSubject, string $sustainingMembershipSubject ) {
		$this->translator = $translator;
		$this->activeMembershipSubject = $activeMembershipSubject;
		$this->sustainingMembershipSubject = $sustainingMembershipSubject;
	}

	public function render( array $templateArguments = [] ): string {
		if ( $templateArguments['membershipType'] === Application::ACTIVE_MEMBERSHIP ) {
			return $this->translator->trans( $this->activeMembershipSubject );
		}
		return $this->translator->trans( $this->sustainingMembershipSubject );
	}

}
