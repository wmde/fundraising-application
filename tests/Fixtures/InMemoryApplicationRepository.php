<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryApplicationRepository implements ApplicationRepository {

	/**
	 * @var \WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application[]
	 */
	private $applications = [];

	private $nextNewId = 1;

	/**
	 * @param \WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application $application
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
	 * @return \WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application|null
	 * @throws \WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		return array_key_exists( $id, $this->applications ) ? $this->applications[$id] : null;
	}

}