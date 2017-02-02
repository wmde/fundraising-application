<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationAcceptedEventHandler {

	public const AUTHORIZATION_FAILED = 'Authorization failed';
	public const UNKNOWN_ID_PROVIDED = 'Unknown donation id';
	public const DATABASE_ERROR_OCCURRED = 'Database error occurred';
	public const SUCCESS = null;

	private $authorizer;
	private $repository;
	private $mailer;

	public function __construct( DonationAuthorizer $authorizer, DonationRepository $repository, DonationConfirmationMailer $mailer ) {
		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->mailer = $mailer;
	}

	/**
	 * @param int $donationId
	 *
	 * @return string|null Null on success, string with error message otherwise
	 */
	public function onDonationAccepted( int $donationId ) {
		if ( !$this->authorizer->systemCanModifyDonation( $donationId ) ) {
			return self::AUTHORIZATION_FAILED;
		}

		try {
			$donation = $this->repository->getDonationById( $donationId );
		}
		catch ( GetDonationException $ex ) {
			return self::DATABASE_ERROR_OCCURRED;
		}

		if ( $donation === null ) {
			return self::UNKNOWN_ID_PROVIDED;
		}

		// TODO: handle exceptions
		$this->mailer->sendConfirmationMailFor( $donation );

		return self::SUCCESS;
	}

}
