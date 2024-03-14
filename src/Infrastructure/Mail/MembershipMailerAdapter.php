<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\MembershipContext\Infrastructure\TemplateMailerInterface as MembershipMailerInterface;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\Notification\ApplyForMembershipTemplateArguments;

/**
 * This is an adapter class that converts the ApplyForMembershipTemplateArguments into an array
 * that the {@see TemplateMailerInterface} can handle.
 */
class MembershipMailerAdapter implements MembershipMailerInterface {
	public function __construct(
		private readonly TemplateMailerInterface $templateMailer
	) {
	}

	public function sendMail( EmailAddress $recipient, ApplyForMembershipTemplateArguments $templateArguments ): void {
		$this->templateMailer->sendMail( $recipient, get_object_vars( $templateArguments ) );
	}
}
