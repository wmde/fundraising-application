<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use WMDE\Fundraising\Frontend\Domain\PageRetriever;
use WMDE\Fundraising\Frontend\Presenters\Content\PageContentModifier;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	private $pageRetriever;
	private $contentModifier;
	private $pageTitlePrefix;

	public function __construct( PageRetriever $pageRetriever,
								 PageContentModifier $contentModifier,
								 string $pageTitlePrefix = '' ) {
		$this->pageRetriever = $pageRetriever;
		$this->contentModifier = $contentModifier;
		$this->pageTitlePrefix = $pageTitlePrefix;
	}

	public function getPage( PageDisplayRequest $listingRequest ): PageDisplayResponse {
		$response = new PageDisplayResponse();

		$response->setHeaderContent( $this->getPageContent( $this->getPrefixedPageTitle( '10hoch16/Seitenkopf' ) ) );
		$response->setMainContent( $this->getPageContent( $this->getPrefixedPageTitle( $listingRequest->getPageName() ) ) );
		$response->setFooterContent( $this->getPageContent( $this->getPrefixedPageTitle( '10hoch16/Seitenfuß' ) ) );
		$response->setNoJsNoticedContent( $this->getPageContent( $this->getPrefixedPageTitle( 'JavaScript-Notice' ) ) );

		$response->freeze();
		$response->assertNoNullFields();

		return $response;
	}

	private function getPageContent( string $pageName ): string {
		$normalizedPageName = $this->normalizePageName( $pageName );

		// TODO: whitelisting and blacklisting of page name?
		// TODO: debug output when dev?

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
