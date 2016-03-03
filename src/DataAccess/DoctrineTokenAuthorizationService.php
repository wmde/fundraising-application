<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationService;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineTokenAuthorizationService implements AuthorizationService {

	private $entityManager;
	private $updateToken;

	public function __construct( EntityManager $entityManager, string $updateToken ) {
		$this->entityManager = $entityManager;
		$this->updateToken = $updateToken;
	}

	public function canModifyDonation( int $donationId ): bool {
		try {
			/**
			 * @var Donation $donation
			 */
			$donation = $this->entityManager->find( Donation::class, $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		if ( $donation === null ) {
			return false;
		}

		$data = $donation->getDecodedData();

		return is_array( $data )
			&& $this->tokenMatches( $data )
			&& $this->tokenHasNotExpired( $data );
	}

	private function tokenMatches( array $data ): bool {
		return array_key_exists( 'utoken', $data )
			&& $data['utoken'] === $this->updateToken;
	}

	private function tokenHasNotExpired( array $data ): bool {
		return array_key_exists( 'uexpiry', $data )
			&& strtotime( $data['uexpiry'] ) >= time();
	}

}
