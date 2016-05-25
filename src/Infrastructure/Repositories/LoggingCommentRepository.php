<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Repositories;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Domain\CommentFinder;
use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingCommentRepository implements CommentRepository, CommentFinder {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $repository;
	private $commentFinder;
	private $logger;
	private $logLevel;

	public function __construct( CommentRepository $repository, CommentFinder $commentFinder, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->commentFinder = $commentFinder;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @see CommentRepository::storeComment
	 *
	 * @param Comment $comment
	 * @throws StoreCommentException
	 */
	public function storeComment( Comment $comment ) {
		try {
			$this->repository->storeComment( $comment );
		}
		catch ( StoreCommentException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @see CommentFinder::getPublicComments
	 *
	 * @param int $limit
	 *
	 * @return CommentWithAmount[]
	 * @throws \Exception
	 */
	public function getPublicComments( int $limit ): array {
		try {
			return $this->commentFinder->getPublicComments( $limit );
		}
		catch ( \Exception $ex ) { // TODO: use more specific exception type
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}
}
