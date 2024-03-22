<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\DomCrawler\Crawler;

trait GetApplicationVarsTrait {
	private function getDataApplicationVars( Crawler $crawler ): \stdClass {
		/** @var \DOMElement $appElement */
		$appElement = $crawler->filter( '#appdata' )->getNode( 0 );
		$decodedApplicationVars = json_decode( $appElement->getAttribute( 'data-application-vars' ) );
		if ( !( $decodedApplicationVars instanceof \stdClass ) ) {
			throw new \RuntimeException( 'Could not decode application vars as an object' );
		}
		return $decodedApplicationVars;
	}
}
