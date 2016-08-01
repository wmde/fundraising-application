<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentListingException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingCommentFinder implements CommentFinder {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $commentFinder;
	private $logger;
	private $logLevel;

	public function __construct( CommentFinder $commentFinder, LoggerInterface $logger ) {
		$this->commentFinder = $commentFinder;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @see CommentFinder::getPublicComments
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentWithAmount[]
	 * @throws \WMDE\Fundraising\Frontend\DonatingContext\Domain\\WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentListingException
	 */
	public function getPublicComments( int $limit, int $offset = 0 ): array {
		try {
			return $this->commentFinder->getPublicComments( $limit, $offset );
		}
		catch ( CommentListingException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}
}
