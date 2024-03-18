<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;

class BasicMailSubjectRenderer implements MailSubjectRendererInterface {

	private TranslatorInterface $translator;
	private string $subjectKey;

	public function __construct( TranslatorInterface $translator, string $subjectKey ) {
		$this->translator = $translator;
		$this->subjectKey = $subjectKey;
	}

	public function render( array $templateArguments = [] ): string {
		return $this->translator->trans( $this->subjectKey );
	}

}
