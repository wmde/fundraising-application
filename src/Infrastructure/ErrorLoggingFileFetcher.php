<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerInterface;

class ErrorLoggingFileFetcher implements FileFetcher {

	private $wrappedFileFetcher;

	private $logger;

	public function __construct( FileFetcher $fileFetcher, LoggerInterface $logger ) {
		$this->wrappedFileFetcher = $fileFetcher;
		$this->logger = $logger;
	}

	/**
	 * @inheritdoc
	 */
	public function fetchFile( $fileUrl ) {
		try {
			return $this->wrappedFileFetcher->fetchFile( $fileUrl );
		} catch ( FileFetchingException $e ) {
			$this->logger->error( $e->getMessage(), [
				'exception' => $e
			] );
			throw $e;
		}
	}

}
