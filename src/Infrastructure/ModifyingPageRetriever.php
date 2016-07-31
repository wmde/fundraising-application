<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\PageRetriever;
use WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ModifyingPageRetriever implements PageRetriever {

	private $pageRetriever;
	private $contentModifier;
	private $pageTitlePrefix;

	public function __construct( PageRetriever $pageRetriever, PageContentModifier $contentModifier, string $pageTitlePrefix ) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
		$this->pageTitlePrefix = $pageTitlePrefix;
	}

	public function fetchPage( string $pageName, string $fetchMode ): string {
		$normalizedPageName = $this->normalizePageName( $this->getPrefixedPageTitle( $pageName ) );

		$content = $this->pageRetriever->fetchPage( $normalizedPageName, $fetchMode );
		$content = $this->contentModifier->getProcessedContent( $content, $normalizedPageName );

		return $content;
	}

	// TODO: seems misplaced. Should likely either be handled near input, or before sending to API or similar
	private function normalizePageName( string $title ): string {
		return ucfirst( str_replace( ' ', '_', trim( $title ) ) );
	}

	private function getPrefixedPageTitle( string $pageTitle ): string {
		return $this->pageTitlePrefix . $pageTitle;
	}

}
