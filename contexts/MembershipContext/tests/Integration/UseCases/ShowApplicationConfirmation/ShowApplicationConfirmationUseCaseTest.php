<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\UseCases\ShowApplicationConfirmation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\FakeApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\FixedApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures\SucceedingAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowAppConfirmationRequest;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationUseCase;

/**
 * @covers \WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowApplicationConfirmationUseCaseTest extends TestCase {

	private const APPLICATION_ID = 42;

	/**
	 * @var ApplicationAuthorizer
	 */
	private $authorizer;

	/**
	 * @var FakeApplicationRepository
	 */
	private $repository;

	/**
	 * @var FixedApplicationTokenFetcher
	 */
	private $tokenFetcher;

	public function setUp() {
		$this->authorizer = new SucceedingAuthorizer();
		$this->repository = new FakeApplicationRepository();
		$this->tokenFetcher = FixedApplicationTokenFetcher::newWithDefaultTokens();
	}

	public function testWhenExceptionIsThrown_failureResponseIsReturned() {
		$this->repository->throwOnRead();

		$request = new ShowAppConfirmationRequest( self::APPLICATION_ID );
		$response = $this->newUseCase()->showConfirmation( $request );

		$this->assertFalse( $response->accessIsPermitted() );
	}

	private function newUseCase(): ShowApplicationConfirmationUseCase {
		return new ShowApplicationConfirmationUseCase(
			$this->authorizer,
			$this->repository,
			$this->tokenFetcher
		);
	}

}
