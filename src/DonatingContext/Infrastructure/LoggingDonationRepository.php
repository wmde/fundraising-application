<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\GetDonationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingDonationRepository implements DonationRepository {

	const CONTEXT_EXCEPTION_KEY = 'exception';

	private $repository;
	private $logger;
	private $logLevel;

	public function __construct( DonationRepository $repository, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->logger = $logger;
		$this->logLevel = LogLevel::CRITICAL;
	}

	/**
	 * @see DonationRepository::storeDonation
	 *
	 * @param \WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation $donation
	 *
	 * @throws \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\StoreDonationException
	 */
	public function storeDonation( Donation $donation ) {
		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\StoreDonationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @see DonationRepository::getDonationById
	 *
	 * @param int $id
	 *
	 * @return \WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation|null
	 * @throws GetDonationException
	 */
	public function getDonationById( int $id ) {
		try {
			return $this->repository->getDonationById( $id );
		}
		catch ( GetDonationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ self::CONTEXT_EXCEPTION_KEY => $ex ] );
			throw $ex;
		}
	}

}
