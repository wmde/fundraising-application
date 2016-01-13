<?php

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

	private $testEnvironment;

	public function __construct( TestEnvironment $testEnvironment ) {
		$this->testEnvironment = $testEnvironment;
	}

	public function __invoke( Request $request ) {
		$pageResponses = [
			'Unicorns' => $this->testEnvironment->getJsonTestData( 'mwApiUnicornsPage.json' ),
			'10hoch16/Seitenkopf' => $this->testEnvironment->getJsonTestData( 'mwApiHeaderPage.json' ),
			'10hoch16/Seitenfuß' => $this->testEnvironment->getJsonTestData( 'mwApiFooterPage.json' ),
		];

		if ( array_key_exists( $request->getParams()['page'], $pageResponses ) ) {
			return $pageResponses[$request->getParams()['page']];
		}

		throw new UsageException( 'Page not found: ' . $request->getParams()['page'] );
	}

}
