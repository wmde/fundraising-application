<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplicationRepositorySpy extends FakeApplicationRepository {

	private $storeApplicationCalls = [];
	private $getApplicationCalls = [];

	public function __construct( Application ...$applications ) {
		parent::__construct( ...$applications );
		$this->storeApplicationCalls = []; // remove calls coming from initialization
	}

	public function storeApplication( Application $application ): void {
		$this->storeApplicationCalls[] = clone( $application ); // protect against the application being changed later
		parent::storeApplication( $application );
	}

	/**
	 * @return Application[]
	 */
	public function getStoreApplicationCalls(): array {
		return $this->storeApplicationCalls;
	}

	/**
	 * @param int $id
	 *
	 * @return Application|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ): ?Application {
		$this->getApplicationCalls[] = $id;
		return parent::getApplicationById( $id );
	}

	/**
	 * @return int[]
	 */
	public function getGetApplicationCalls(): array {
		return $this->getApplicationCalls;
	}

}
