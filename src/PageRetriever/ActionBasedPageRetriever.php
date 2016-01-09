<?php

namespace WMDE\Fundraising\Frontend\PageRetriever;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerInterface;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen
 * @author Christoph Fischer
 */
class ActionBasedPageRetriever implements PageRetriever {

	private $logger;
	private $fetcher;
	private $wikiScriptUrl;

	public function __construct( string $wikiScriptUrl, LoggerInterface $logger, FileFetcher $fetcher ) {
		$this->logger = $logger;
		$this->fetcher = $fetcher;
		$this->wikiScriptUrl = $wikiScriptUrl;
	}

	public function fetchPage( string $pageName ): string {
		$this->logger->debug( __METHOD__ . ': wiki_page', [ $pageName ] );
		$this->logger->debug( __METHOD__ . ': action' );

		$pageUrl = $this->getPageUrl( $pageName );
		$this->logger->debug( __METHOD__ . ': page_url', [ $pageUrl ] );

		try {
			$content = $this->fetcher->fetchFile( $pageUrl );
		}
		catch ( FileFetchingException $ex ) {
			$this->logger->debug( __METHOD__, [ $ex->getMessage() ] );
			return '';
		}

		return $this->checkForCleanResult( $content );
	}

	private function getPageUrl( $pageName ) {
		return $this->wikiScriptUrl . '?title=' . urlencode( $pageName ) . '&action=render';
	}

	private function checkForCleanResult( $content ) {
		// full HTML document usually indicates an error (e.g. access denied)
		if ( preg_match( '/^<!DOCTYPE html/', $content ) ) {
			$this->logger->debug( __METHOD__ . ': fail, got error page', [ $content ] );
			return '';
		}

		return $content;
	}

}
