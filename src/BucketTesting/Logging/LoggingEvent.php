<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

interface LoggingEvent {
	public function getMetaData(): array;
	public function getName(): string;
}