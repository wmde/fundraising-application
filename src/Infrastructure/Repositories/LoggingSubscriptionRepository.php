<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Repositories;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\SubscriptionRepositoryException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingSubscriptionRepository implements SubscriptionRepository {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $repository;
	private $logger;
	private $logLevel;

	public function __construct( SubscriptionRepository $repository, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @see SubscriptionRepository::storeSubscription
	 *
	 * @param Subscription $subscription
	 *
	 * @throws SubscriptionRepositoryException
	 */
	public function storeSubscription( Subscription $subscription ) {
		try {
			$this->repository->storeSubscription( $subscription );
		}
		catch ( SubscriptionRepositoryException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @see SubscriptionRepository::countSimilar
	 *
	 * @param Subscription $subscription
	 * @param \DateTime $cutoffDateTime
	 *
	 * @return int
	 * @throws SubscriptionRepositoryException
	 */
	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int {
		try {
			return $this->repository->countSimilar( $subscription, $cutoffDateTime );
		}
		catch ( SubscriptionRepositoryException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @see SubscriptionRepository::findByConfirmationCode
	 *
	 * @param string $confirmationCode
	 *
	 * @return Subscription|null
	 * @throws SubscriptionRepositoryException
	 */
	public function findByConfirmationCode( string $confirmationCode ) {
		try {
			return $this->repository->findByConfirmationCode( $confirmationCode );
		}
		catch ( SubscriptionRepositoryException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

}
