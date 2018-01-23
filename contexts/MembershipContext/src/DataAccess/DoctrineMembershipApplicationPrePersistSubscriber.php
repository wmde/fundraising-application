<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\DataAccess;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipTokenGenerator;
use WMDE\Fundraising\Store\MembershipApplicationData;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationPrePersistSubscriber implements EventSubscriber {

	private $updateTokenGenerator;
	private $accessTokenGenerator;

	public function __construct( MembershipTokenGenerator $updateTokenGenerator, MembershipTokenGenerator $accessTokenGenerator ) {
		$this->updateTokenGenerator = $updateTokenGenerator;
		$this->accessTokenGenerator = $accessTokenGenerator;
	}

	public function getSubscribedEvents(): array {
		return [ Events::prePersist ];
	}

	public function prePersist( LifecycleEventArgs $args ): void {
		$entity = $args->getObject();

		if ( $entity instanceof MembershipApplication ) {
			$entity->modifyDataObject( function ( MembershipApplicationData $data ): void {
				if ( $this->isEmpty( $data->getAccessToken() ) ) {
					$data->setAccessToken( $this->accessTokenGenerator->generateToken() );
				}

				if ( $this->isEmpty( $data->getUpdateToken() ) ) {
					$data->setUpdateToken( $this->updateTokenGenerator->generateToken() );
				}
			} );
		}
	}

	private function isEmpty( ?string $stringOrNull ): bool {
		return $stringOrNull === null || $stringOrNull === '';
	}

}