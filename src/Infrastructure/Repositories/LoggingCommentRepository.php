<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Repositories;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Domain\CommentFinder;
use WMDE\Fundraising\Frontend\Domain\CommentListingException;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingCommentRepository implements CommentFinder {

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
	 *
	 * @return CommentWithAmount[]
	 * @throws CommentListingException
	 */
	public function getPublicComments( int $limit ): array {
		try {
			return $this->commentFinder->getPublicComments( $limit );
		}
		catch ( CommentListingException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}
}
