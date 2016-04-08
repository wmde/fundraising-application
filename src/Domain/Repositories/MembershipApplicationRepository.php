<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Repositories;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface MembershipApplicationRepository {

	/**
	 * When storing a not yet persisted MembershipApplication, a new id will be generated and assigned to it.
	 * Any previously set ID will be overridden. The id can be accessed by calling getId on
	 * the passed in MembershipApplication.
	 *
	 * @param MembershipApplication $application
	 *
	 * @throws StoreMembershipApplicationException
	 */
	public function storeApplication( MembershipApplication $application );

	/**
	 * @param int $id
	 *
	 * @return MembershipApplication|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id );

}
