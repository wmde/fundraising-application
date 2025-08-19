<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipFeeUpgradeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( ShowMembershipFeeUpgradeController::class )]
class ShowMembershipFeeUpgradeRouteTest extends WebRouteTestCase {

	private const PATH = '/show-membership-confirmation';
	private const WRONG_ACCESS_TOKEN = 'foobar';
	private const VALID_ACCESS_TOKEN = 9998;

	public function testNoUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$this->assertFalse();
	}

	public function testInvalidUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$this->assertFalse();
	}

	public function testValidUUIDInRequest_rendersFeeUpgradeForm(): void {
		$this->assertFalse();
	}


}
