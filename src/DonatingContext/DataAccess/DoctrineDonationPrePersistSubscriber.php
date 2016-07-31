<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\DataAccess;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Store\DonationData;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationPrePersistSubscriber implements EventSubscriber {

	/* private */ const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

	private $updateTokenGenerator;
	private $accessTokenGenerator;

	public function __construct( TokenGenerator $updateTokenGenerator, TokenGenerator $accessTokenGenerator ) {
		$this->updateTokenGenerator = $updateTokenGenerator;
		$this->accessTokenGenerator = $accessTokenGenerator;
	}

	public function getSubscribedEvents(): array {
		return [ Events::prePersist ];
	}

	public function prePersist( LifecycleEventArgs $args ) {
		$entity = $args->getObject();

		if ( $entity instanceof Donation ) {
			$entity->modifyDataObject( function ( DonationData $data ) {
				if ( $this->isEmpty( $data->getAccessToken() ) ) {
					$data->setAccessToken( $this->accessTokenGenerator->generateToken() );
				}

				if ( $this->isEmpty( $data->getUpdateToken() ) ) {
					$data->setUpdateToken( $this->updateTokenGenerator->generateToken() );
				}

				if ( $this->isEmpty( $data->getUpdateTokenExpiry() ) ) {
					$expiry = $this->updateTokenGenerator->generateTokenExpiry();
					$data->setUpdateTokenExpiry( $expiry->format( self::DATE_TIME_FORMAT ) );
				}
			} );
		}
	}

	private function isEmpty( $stringOrNull ): bool {
		return $stringOrNull === null || $stringOrNull === '';
	}

}