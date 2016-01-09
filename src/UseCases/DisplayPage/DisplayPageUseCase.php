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

	public function getPage( PageDisplayRequest $listingRequest ): string {
		$pageContent = $this->getPageContent( $this->normalizePageName( $listingRequest->getPageName() ) );

		return "<html><header />$pageContent</html>";
	}

	private function getPageContent( string $pageName ) {
		// TODO: fetch template and embed page content into it
		// TODO: whitelisting and blacklisting of page name?
		// TODO: debug output when dev?

		$content = $this->pageRetriever->fetchPage( $pageName );
		$content = $this->contentModifier->getProcessedContent( $content, $pageName );

		if ( $content !== '' ) {
			return $content;
		}

		return 'missing: ' . htmlspecialchars( $pageName );
	}

	private function normalizePageName( string $title ): string {
		return ucfirst( str_replace( ' ', '_', trim( $title ) ) );
	}

}
