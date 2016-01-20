<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class MailAddress {
	public $userName;
	public $domain;

	public function __construct( string $userName, string $domain ) {
		$this->userName = $userName;
		$this->domain = $domain;
	}

	public function __toString(): string {
		return $this->getString();
	}

	public function getString(): string {
		return $this->userName . '@' . $this->domain;
	}
}
