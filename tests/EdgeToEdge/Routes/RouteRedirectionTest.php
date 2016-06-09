<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class RouteRedirectionTest extends WebRouteTestCase {

	public function simplePageDisplayProvider() {
		return [
			[ '/spenden/Mitgliedschaft', '/page/Mitgliedschaft' ],
			[ '/spenden/Fördermitgliedschaft', '/page/Fördermitgliedschaft' ],
			[ '/spenden/Kontaktformular', '/page/Kontaktformular' ],
			[ '/spenden/Ihre_Spende_wirkt', '/page/Ihre_Spende_wirkt' ],
			[ '/spenden/Mitgliedschaft?bar=baz&foo=bar', '/page/Mitgliedschaft?bar=baz&foo=bar' ],
		];
	}

	/** @dataProvider simplePageDisplayProvider */
	public function testPageDisplayRequestsAreRedirected( $requestedUrl, $expectedRedirection ) {
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request( 'GET', $requestedUrl );
		$response = $client->getResponse();

		$this->assertTrue( $response->isRedirect() );
		$this->assertSame( $expectedRedirection, $response->headers->get( 'Location' ) );
	}

	public function shouldRedirectToDefaultRouteProvider() {
		return [
			[ '/spenden/spende.php', '/' ],
			[ '/spenden/contact.php', '/' ],
			[ '/spenden/?', '/' ],
			[ '/spenden/?bar=baz&foo=bar', '/?bar=baz&foo=bar' ],
			[ '/spenden?bar=baz&foo=bar', '/?bar=baz&foo=bar' ],
			[ '/spenden/', '/' ],
			[ '/spenden', '/' ],
		];
	}

	/** @dataProvider shouldRedirectToDefaultRouteProvider */
	public function testRequestsAreRedirectedToDefaultRoute( $requestedUrl, $expectedRedirection ) {
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request( 'GET', $requestedUrl );
		$response = $client->getResponse();

		$this->assertTrue( $response->isRedirect() );
		$this->assertSame( $expectedRedirection, $response->headers->get( 'Location' ) );
	}

	public function commentListUrlProvider() {
		return [
			[ '/spenden/list.php', '/list-comments.html' ],
			[ '/spenden/rss.php', '/list-comments.rss' ],
			[ '/spenden/json.php', '/list-comments.json' ]
		];
	}

	/** @dataProvider commentListUrlProvider */
	public function testCallsToCommentListIsRedirected( $requestedUrl, $expectedRedirection ) {
		$client = $this->createClient();
		$client->followRedirects( false );
		$client->request( 'GET', $requestedUrl );
		$response = $client->getResponse();

		$this->assertTrue( $response->isRedirect() );
		$this->assertSame( $expectedRedirection, $response->headers->get( 'Location' ) );
	}

}
