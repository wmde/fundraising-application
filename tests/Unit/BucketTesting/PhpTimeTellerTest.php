<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\PhpTimeTeller;

class PhpTimeTellerTest extends TestCase {

	public function testUsesPhpDate() {
		$this->assertStringEndsWith(
			'.000+00:00',
			( new PhpTimeTeller() )->getTime()
		);
	}

	public function testUsesFormatParameter() {
		$this->assertRegExp(
			'/^\d{4}$/',
			( new PhpTimeTeller( 'Y' ) )->getTime()
		);
	}

}
