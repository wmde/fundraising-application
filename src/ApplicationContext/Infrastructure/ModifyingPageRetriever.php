<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure;

use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\PageRetriever;
use WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ModifyingPageRetriever implements PageRetriever {

	private $pageRetriever;
	private $contentModifier;

	public function __construct( PageRetriever $pageRetriever, PageContentModifier $contentModifier ) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
	}

	public function fetchPage( string $pageName, string $fetchMode ): string {
		$content = $this->pageRetriever->fetchPage( $pageName, $fetchMode );
		$content = $this->contentModifier->getProcessedContent( $content, $pageName );

		return $content;
	}
}
