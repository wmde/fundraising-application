<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

interface TimeTeller {

	public function getTime(): string;

}