<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

interface LogWriter {

	/**
	 * @throws LoggingError
	 */
	public function write( string $logEntry );

}
