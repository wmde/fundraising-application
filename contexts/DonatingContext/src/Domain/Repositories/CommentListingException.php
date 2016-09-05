<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListingException extends \RuntimeException {

	public function __construct( \Exception $previous = null ) {
		parent::__construct( 'Could not list comments', 0, $previous );
	}

}
