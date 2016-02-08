<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SubscriptionRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
use Swift_NullTransport;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentRouteTest extends WebRouteTestCase {

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'add-comment',
			[]
		);
	}

}
