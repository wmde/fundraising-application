<?php

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use WMDE\Fundraising\Frontend\PageRetriever\PageRetriever;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	private $pageRetriever;
	private $contentModifier;

	public function __construct( PageRetriever $pageRetriever, PageContentModifier $contentModifier ) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
	}

	public function getPage( PageDisplayRequest $listingRequest ): PageDisplayResponse {
		$response = new PageDisplayResponse();

		$response->setHeaderContent( $this->getPageContent( '10hoch16/Seitenkopf' ) );
		$response->setMainContent( $this->getPageContent( $listingRequest->getPageName() ) );
		$response->setFooterContent( $this->getPageContent( '10hoch16/Seitenfuß' ) );

		$response->freeze();
		$response->assertNoNullFields();
		return $response;
	}

	private function getPageContent( string $pageName ): string {
		$normalizedPageName = $this->normalizePageName( $pageName );

		// TODO: fetch template and embed page content into it
		// TODO: whitelisting and blacklisting of page name?
		// TODO: debug output when dev?

		$content = $this->pageRetriever->fetchPage( $normalizedPageName );
		$content = $this->contentModifier->getProcessedContent( $content, $normalizedPageName );

		if ( $content !== '' ) {
			return $content;
		}

		return 'missing: ' . $normalizedPageName;
	}

	private function normalizePageName( string $title ): string {
		return ucfirst( str_replace( ' ', '_', trim( $title ) ) );
	}

}
