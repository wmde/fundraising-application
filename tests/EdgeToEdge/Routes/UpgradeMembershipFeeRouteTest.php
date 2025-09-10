<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\API\Membership\UpgradeMembershipFeeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( UpgradeMembershipFeeController::class )]
class UpgradeMembershipFeeRouteTest extends WebRouteTestCase {

	// TODO
	// test different input values for the input params (int, string, invalid payment types)
}
