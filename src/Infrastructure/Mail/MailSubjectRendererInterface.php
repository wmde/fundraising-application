<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

interface MailSubjectRendererInterface {

	/**
	 * @param array<string, mixed> $templateArguments
	 */
	public function render( array $templateArguments = [] ): string;

}
