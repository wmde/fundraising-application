<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryMembershipApplicationRepository implements MembershipApplicationRepository {

	/**
	 * @var MembershipApplication[]
	 */
	private $applications = [];

	private $nextNewId = 1;

	/**
	 * @param MembershipApplication $application
	 *
	 * @throws StoreMembershipApplicationException
	 */
	public function storeApplication( MembershipApplication $application ) {
		if ( !$application->hasId() ) {
			$application->assignId( $this->nextNewId++ );
		}

		$this->applications[$application->getId()] = $application;
	}

	/**
	 * @param int $id
	 *
	 * @return MembershipApplication|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		return array_key_exists( $id, $this->applications ) ? $this->applications[$id] : null;
	}

}