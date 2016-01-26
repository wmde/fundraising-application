<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Mediawiki\Api\Request;
use Mediawiki\Api\UsageException;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * TODO: once https://github.com/addwiki/mediawiki-api-base/pull/20 is merged, we can create
 * a simple fake similar to InMemoryFileFetcher.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiPostRequestHandler {

	public function __invoke( Request $request ) {
		$pageResponses = [
			'Unicorns' => TestEnvironment::getJsonTestData( 'mwApiUnicornsPage.json' ),
			'10hoch16/Seitenkopf' => TestEnvironment::getJsonTestData( 'mwApiHeaderPage.json' ),
			'10hoch16/Seitenfuß' => TestEnvironment::getJsonTestData( 'mwApiFooterPage.json' ),
			'MyNamespace:MyPrefix/Naked_mole-rat' => TestEnvironment::getJsonTestData( 'mwApiPrefixedTitlePage.json' ),
			'JavaScript-Notice' => TestEnvironment::getJsonTestData( 'mwApiJsNoticePage.json' ),
			'SubscriptionForm' => TestEnvironment::getJsonTestData( 'mwApiSubscriptionForm.json' ),
		];

		if ( array_key_exists( $request->getParams()['page'], $pageResponses ) ) {
			return $pageResponses[$request->getParams()['page']];
		}

		throw new UsageException( 'Page not found: ' . $request->getParams()['page'] );
	}

}
