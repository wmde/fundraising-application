<?php

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	private $fileFetcher;
	private $urlBase;

	public function __construct( FileFetcher $fileFetcher, string $urlBase ) {
		$this->fileFetcher = $fileFetcher;
		$this->urlBase = $urlBase;
	}

	public function getPage( PageDisplayRequest $listingRequest ): string {
		$pageContent = $this->getPageContent( $listingRequest->getPageName() );

		return "<html><header />$pageContent</html>";
	}

	private function getPageContent( string $pageName ) {
		// Normalization
		// White and blacklisting of page name
		// pageRetriever->fetchPage( $wiki_page, 'render' )
		// getProcessedContent( $content, $wiki_page, 'render' )
		// Debug output when dev

		try {
			$content = $this->fileFetcher->fetchFile( $this->urlBase . $pageName );
		}
		catch ( FileFetchingException $ex ) {
			$content = '';
		}

		if ( $content === '' ) {
			return 'missing: ' . htmlspecialchars( $pageName );
		}

		return $content;
	}

}
