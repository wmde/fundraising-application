<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

interface MailSubjectRendererInterface {

	public function render( array $templateArguments = [] ): string;

}
