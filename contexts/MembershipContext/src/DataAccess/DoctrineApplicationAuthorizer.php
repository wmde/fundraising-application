<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Authorization\ApplicationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineApplicationAuthorizer implements ApplicationAuthorizer {

	private $entityManager;
	private $updateToken;
	private $accessToken;

	public function __construct( EntityManager $entityManager, string $updateToken = null, string $accessToken = null ) {
		$this->entityManager = $entityManager;
		$this->updateToken = $updateToken;
		$this->accessToken = $accessToken;
	}

	public function canModifyApplication( int $applicationId ): bool {
		try {
			$application = $this->getApplicationById( $applicationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		return $application !== null
			&& $this->updateTokenMatches( $application );
	}

	private function getApplicationById( int $applicationId ): ?MembershipApplication {
		return $this->entityManager->find( MembershipApplication::class, $applicationId );
	}

	private function updateTokenMatches( MembershipApplication $application ): bool {
		return hash_equals( (string)$application->getDataObject()->getUpdateToken(), (string)$this->updateToken );
	}

	public function canAccessApplication( int $applicationId ): bool {
		try {
			$application = $this->getApplicationById( $applicationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		return $application !== null
			&& $this->accessTokenMatches( $application );
	}

	private function accessTokenMatches( MembershipApplication $application ): bool {
		return hash_equals( (string)$application->getDataObject()->getAccessToken(), (string)$this->accessToken );
	}

}
