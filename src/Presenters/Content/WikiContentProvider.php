<?php

namespace WMDE\Fundraising\Frontend\Presenters\Content;

use WMDE\Fundraising\Frontend\Domain\PageRetriever;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class WikiContentProvider {

	private $pageRetriever;
	private $contentModifier;
	private $pageTitlePrefix;
	private $pageCache = [];
	private $errorCache = [];

	public function __construct( PageRetriever $pageRetriever, PageContentModifier $contentModifier, string $pageTitlePrefix) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
		$this->pageTitlePrefix = $pageTitlePrefix;
	}

	public function getContent( string $pageName ): string {
		$prefixedPageName = $this->getPrefixedPageTitle( $pageName );
		$normalizedPageName = $this->normalizePageName( $prefixedPageName );
		$content = $this->pageRetriever->fetchPage( $normalizedPageName );
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