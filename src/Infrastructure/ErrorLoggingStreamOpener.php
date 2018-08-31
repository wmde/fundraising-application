<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Psr\Log\LoggerInterface;

/**
 * This class catches StreamOpeningErrors and returns a throwaway stream
 *
 * @license GNU GPL v2+
 */
class ErrorLoggingStreamOpener implements StreamOpener {

	private $logger;
	private $streamOpener;

	public function __construct( StreamOpener $streamOpener, LoggerInterface $logger ) {
		$this->logger = $logger;
		$this->streamOpener = $streamOpener;
	}

	/**
	 * @return resource
	 */
	public function openStream( string $url, string $mode ) {
		try {
			return $this->streamOpener->openStream( $url, $mode );
		} catch ( StreamOpeningError $e ) {
			$this->logger->error( $e->getMessage(), [ 'url' => $url, 'mode' => $mode ] );
			return fopen( 'php://temp', $mode );
		}
	}
}