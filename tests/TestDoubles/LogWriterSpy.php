<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LogWriter;

class LogWriterSpy implements LogWriter {

	private $entries = [];

	public function getWriteCalls(): array {
		return $this->entries;
	}

	public function write( string $logEntry ): void {
		$this->entries[] = $logEntry;
	}

}
