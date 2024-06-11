<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\DonationContext\Domain\Model\ModerationIdentifier as DonationModerationIdentifier;
use WMDE\Fundraising\MembershipContext\Domain\Model\ModerationIdentifier as MembershipModerationIdentifier;

class AdminModerationMailRenderer implements MailSubjectRendererInterface {

	/**
	 * @param array<string, mixed> $templateArguments
	 */
	public function render( array $templateArguments = [] ): string {
		if ( isset( $templateArguments['moderationFlags'][MembershipModerationIdentifier::MEMBERSHIP_FEE_TOO_HIGH->name] ) ) {
			return "[Mitgliedschaftenmoderation] Ein Mitgliedschaftsantrag hat einen ungewöhnlich hohen Betrag";
		}
		if ( isset( $templateArguments['moderationFlags'][DonationModerationIdentifier::AMOUNT_TOO_HIGH->name] ) ) {
			return "[Spendenmoderation] Eine Spende hat einen ungewöhnlich hohen Betrag";
		}

		// this should never happen
		// only if the fundraising team wants more mails for different moderation reasons
		$moderationFlags = implode( ",", array_keys( $templateArguments['moderationFlags'] ) );
		return "Ein Moderationsgrund wurde ausgelöst: " . $moderationFlags;
	}
}
