<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationTokenFetchingException;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\MembershipApplicationTokens;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineApplicationTokenFetcher implements ApplicationTokenFetcher {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @param int $applicationId
	 *
	 * @return MembershipApplicationTokens
	 * @throws ApplicationTokenFetchingException
	 */
	public function getTokens( int $applicationId ): MembershipApplicationTokens {
		$application = $this->getApplicationById( $applicationId );

		return new MembershipApplicationTokens(
			$application->getDataObject()->getAccessToken(),
			$application->getDataObject()->getUpdateToken()
		);
	}

	private function getApplicationById( int $applicationId ): ?MembershipApplication {
		return $this->entityManager->find( MembershipApplication::class, $applicationId );
	}

}
