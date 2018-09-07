<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LogWriter;

class LogWriterSpy implements LogWriter {

	private $entries = [];

	public function getWriteCalls(): array {
		return $this->entries;
	}

	public function write( string $logEntry ) {
		$this->entries[] = $logEntry;
	}

}
