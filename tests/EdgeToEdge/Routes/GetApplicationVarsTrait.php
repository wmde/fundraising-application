<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\DomCrawler\Crawler;

trait GetApplicationVarsTrait {
	private function getDataApplicationVars( Crawler $crawler ): object {
		/** @var \DOMElement $appElement */
		$appElement = $crawler->filter( '#appdata' )->getNode( 0 );
		return json_decode( $appElement->getAttribute( 'data-application-vars' ) );
	}
}
