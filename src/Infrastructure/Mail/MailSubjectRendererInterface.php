<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

/**
 * @license GPL-2.0-or-later
 */
interface MailSubjectRendererInterface {

	public function render( array $templateArguments = [] ): string;

}
