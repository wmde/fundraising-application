<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @license GNU GPL v2+
 */
class MembershipConfirmationMailSubjectRenderer implements MailSubjectRendererInterface {

	private $translator;
	private $activeMembershipSubject;
	private $sustainingMembershipSubject;

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
