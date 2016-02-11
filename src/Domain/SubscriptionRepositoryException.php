<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionRepositoryException extends \RuntimeException {

	public function __construct( string $message, \Exception $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}