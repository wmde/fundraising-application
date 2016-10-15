<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\PageRetriever;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen
 */
class LocalFilePageRetriever implements PageRetriever {

	private $logger;
	private $fetcher;

	public function __construct( FileFetcher $fetcher, LoggerInterface $logger ) {
		$this->logger = $logger;
		$this->fetcher = $fetcher;
	}

	public function fetchPage( string $filename, string $fetchMode = '' ): string {
		$this->logger->debug( __METHOD__ . ': wiki_page', [ $filename ] );

		try {
			$content = $this->fetcher->fetchFile( $filename );
		}
		catch ( FileFetchingException $ex ) {
			$this->logger->debug( __METHOD__, [ $ex->getMessage() ] );
			return '';
		}

		return $content;
	}
}
