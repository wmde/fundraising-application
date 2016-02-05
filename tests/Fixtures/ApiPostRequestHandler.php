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
		switch ( $request->getParams()['action'] ) {
			case 'parse':
				return $this->getPageResponse( $request );
			case 'query':
				return $this->getRawResponse( $request );
			default:
				throw new UsageException( 'Unsupported API action: ' . $request->getParams()['action'] );
		}
	}

	private function getPageResponse( Request $request ) {
		$pageResponses = [
			'Unicorns' => TestEnvironment::getJsonTestData( 'mwApiUnicornsPage.json' ),
			'MyNamespace:MyPrefix/Naked_mole-rat' => TestEnvironment::getJsonTestData( 'mwApiPrefixedTitlePage.json' ),
		];

		if ( array_key_exists( $request->getParams()['page'], $pageResponses ) ) {
			return $pageResponses[$request->getParams()['page']];
		}

		throw new UsageException( 'Page not found: ' . $request->getParams()['page'] );
	}

	private function getRawResponse( Request $request ) {
		$rawResponses = [
			'No_Cats' => TestEnvironment::getJsonTestData( 'mwApiNo_CatsQuery.json' )
		];

		if ( array_key_exists( $request->getParams()['titles'], $rawResponses ) ) {
			return $rawResponses[$request->getParams()['titles']];
		}

		throw new \RuntimeException( 'Page not found: ' . $request->getParams()['titles'] );
	}
}
