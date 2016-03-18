<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	public function testGivenPostRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'show-donation-confirmation',
			[]
		);
	}

}
