<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TemplatedMessage implements Message {

	private $subject;
	private $template;
	private $templateParams = [];

	public function __construct( string $subject, TwigTemplate $template ) {
		$this->subject = $subject;
		$this->template = $template;
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function getMessageBody(): string {
		return $this->template->render( $this->templateParams );
	}

	public function setTemplateParams( array $templateParams ) {
		$this->templateParams = $templateParams;
	}

}
