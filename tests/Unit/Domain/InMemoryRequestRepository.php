<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\InMemoryRequestRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class InMemoryRequestRepositoryTest  extends \PHPUnit_Framework_TestCase
{
	public function testWhenRepositoryIsInitialized_ItContainsNoRequests() {
		$repo = new InMemoryRequestRepository( [] );
		$this->assertEquals( [], $repo->getRequests() );
	}

	public function testRequestsAreStored() {
		$request = $this->getMock( Request::class );
		$repo = new InMemoryRequestRepository( [] );
		$repo->storeRequest( $request );
		$this->assertEquals( [ $request ], $repo->getRequests() );
	}

}