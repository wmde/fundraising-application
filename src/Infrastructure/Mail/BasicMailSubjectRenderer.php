<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * @license GNU GPL v2+
 */
class BasicMailSubjectRenderer implements MailSubjectRendererInterface {

	private $translator;
	private $subjectKey;

	public function __construct( TranslatorInterface $translator, string $subjectKey ) {
		$this->translator = $translator;
		$this->subjectKey = $subjectKey;
	}

	public function render( array $templateArguments = [] ): string {
		return $this->translator->trans( $this->subjectKey );
	}

}
