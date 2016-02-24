<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface Message {

	public function getSubject(): string;
	public function getMessageBody(): string;

}