<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LogWriter;

class LogWriterSpy implements LogWriter {

	/**
	 * @var string[]
	 */
	private array $entries = [];

	/**
	 * @return string[]
	 */
	public function getWriteCalls(): array {
		return $this->entries;
	}

	public function write( string $logEntry ): void {
		$this->entries[] = $logEntry;
	}

}
