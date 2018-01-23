<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingApplicationRepository implements ApplicationRepository {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $repository;
	private $logger;
	private $logLevel;

	public function __construct( ApplicationRepository $repository, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @see MembershipApplicationRepository::storeApplication
	 *
	 * @param Application $application
	 *
	 * @throws StoreMembershipApplicationException
	 */
	public function storeApplication( Application $application ): void {
		try {
			$this->repository->storeApplication( $application );
		}
		catch ( StoreMembershipApplicationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Application|null
	 *
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ): ?Application {
		try {
			return $this->repository->getApplicationById( $id );
		}
		catch ( GetMembershipApplicationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}
}
