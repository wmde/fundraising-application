<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryApplicationRepository implements ApplicationRepository {

	/**
	 * @var Application[]
	 */
	private $applications = [];

	private $nextNewId = 1;

	/**
	 * @param Application $application
	 *
	 * @throws StoreMembershipApplicationException
	 */
	public function storeApplication( Application $application ) {
		if ( !$application->hasId() ) {
			$application->assignId( $this->nextNewId++ );
		}

		$this->applications[$application->getId()] = $application;
	}

	/**
	 * @param int $id
	 *
	 * @return Application|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		return array_key_exists( $id, $this->applications ) ? $this->applications[$id] : null;
	}

}