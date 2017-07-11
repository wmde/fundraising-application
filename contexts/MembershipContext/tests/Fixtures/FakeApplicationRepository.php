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
class FakeApplicationRepository implements ApplicationRepository {

	private $calls = 0;
	private $applications = [];
	private $throwOnRead = false;
	private $throwOnWrite = false;

	public function __construct( Application ...$applications ) {
		foreach ( $applications as $application ) {
			$this->storeApplication( $application );
		}
	}

	public function throwOnRead(): void {
		$this->throwOnRead = true;
	}

	public function throwOnWrite(): void {
		$this->throwOnWrite = true;
	}

	public function storeApplication( Application $application ): void {
		if ( $this->throwOnWrite ) {
			throw new StoreMembershipApplicationException();
		}

		if ( $application->getId() === null ) {
			$application->assignId( ++$this->calls );
		}
		$this->applications[$application->getId()] = unserialize( serialize( $application ) );
	}

	public function getApplicationById( int $id ): ?Application {
		if ( $this->throwOnRead ) {
			throw new GetMembershipApplicationException();
		}

		if ( array_key_exists( $id, $this->applications ) ) {
			return unserialize( serialize( $this->applications[$id] ) );
		}

		return null;
	}

}
