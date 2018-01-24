<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Domain\Repositories;

use WMDE\Fundraising\MembershipContext\Domain\Model\Application;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface ApplicationRepository {

	/**
	 * When storing a not yet persisted MembershipApplication, a new id will be generated and assigned to it.
	 * This means the id of new applications needs to be null. The id can be accessed by calling getId on
	 * the passed in MembershipApplication.
	 *
	 * @param Application $application
	 *
	 * @throws StoreMembershipApplicationException
	 */
	public function storeApplication( Application $application ): void;

	/**
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ): ?Application;

}
