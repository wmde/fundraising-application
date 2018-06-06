<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Payment;

/**
 * TODO: move to own KontoCheck library?
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class KontoCheckLibraryInitializationException extends \RuntimeException {

	public function __construct( ?string $message = null, int $code = null, \Exception $previous = null ) {
		parent::__construct(
			$message !== null ?: 'Could not initialize library with bank data file.' .
				( $code !== null ? ' Reason: ' . kto_check_retval2txt( $code ) : '' ),
			0,
			$previous
		);
	}
}
