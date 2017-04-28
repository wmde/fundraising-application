<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ErrorLoggingFileFetcher implements FileFetcher, LoggerAwareInterface {

	private $wrappedFileFetcher;

	private $logger;

	public function __construct( FileFetcher $fileFetcher, LoggerInterface $logger ) {
		$this->wrappedFileFetcher = $fileFetcher;
		$this->logger = $logger;
	}

	public function fetchFile( $fileUrl ) {
		try {
			return $this->wrappedFileFetcher->fetchFile( $fileUrl );
		} catch ( FileFetchingException $e ) {
			$this->logger->error( $e->getMessage(), [
				'code' => $e->getCode(),
				'trace' => $e->getTraceAsString()
			] );
			return '';
		}
	}

	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}


}