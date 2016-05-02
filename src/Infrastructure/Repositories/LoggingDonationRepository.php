<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Repositories;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LoggingDonationRepository implements DonationRepository {

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
	 * @param Donation $donation
	 *
	 * @throws StoreDonationException
	 */
	public function storeDonation( Donation $donation ) {
		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ 'exception' => $ex ] );
			throw $ex;
		}
	}

	/**
	 * @see DonationRepository::getDonationById
	 *
	 * @param int $id
	 *
	 * @return Donation|null
	 * @throws GetDonationException
	 */
	public function getDonationById( int $id ) {
		try {
			return $this->repository->getDonationById( $id );
		}
		catch ( GetDonationException $ex ) {
			$this->logger->log( $this->logLevel, $ex->getMessage(), [ 'exception' => $ex ] );
			throw $ex;
		}
	}

}
