<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Content;

use WMDE\Fundraising\Frontend\Domain\PageRetriever;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class WikiContentProvider {

	private $pageRetriever;
	private $contentModifier;
	private $pageTitlePrefix;

	public function __construct( PageRetriever $pageRetriever, PageContentModifier $contentModifier, string $pageTitlePrefix ) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
		$this->pageTitlePrefix = $pageTitlePrefix;
	}

	public function getContent( string $pageName, string $fetchMode = 'render' ): string {
		$prefixedPageName = $this->getPrefixedPageTitle( $pageName );
		$normalizedPageName = $this->normalizePageName( $prefixedPageName );
		$content = $this->pageRetriever->fetchPage( $normalizedPageName, $fetchMode );
		$content = $this->contentModifier->getProcessedContent( $content, $normalizedPageName );

		return $content;
	}

	private function normalizePageName( string $title ): string {
		return ucfirst( str_replace( ' ', '_', trim( $title ) ) );
	}

	private function getPrefixedPageTitle( string $pageTitle ): string {
		return $this->pageTitlePrefix . $pageTitle;
	}

}
