<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;

class BasicMailSubjectRenderer implements MailSubjectRendererInterface {

	public function __construct(
		private readonly TranslatorInterface $translator,
		private readonly string $subjectKey
	) {
	}

	/**
	 * @param array<string, mixed> $templateArguments
	 */
	public function render( array $templateArguments = [] ): string {
		return $this->translator->trans( $this->subjectKey );
	}

}
