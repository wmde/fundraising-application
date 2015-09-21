<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class MailAddress {
	public $userName;
	public $domain;

	/**
	 * @param string $userName
	 * @param string $domain
	 */
	public function __construct( $userName, $domain ) {
		$this->userName = $userName;
		$this->domain = $domain;
	}

	public function __toString() {
		return $this->getString();
	}

	public function getString() {
		return $this->userName . "@" . $this->domain;
	}
}
