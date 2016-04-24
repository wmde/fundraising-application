<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;

	/**
	 * @var MembershipApplicationRepository
	 */
	private $repository;

	/**
	 * @var MembershipAppAuthUpdater
	 */
	private $authUpdater;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	public function setUp() {
		$this->repository = new InMemoryMembershipApplicationRepository();
		$this->authUpdater = $this->getMock( MembershipAppAuthUpdater::class );
		$this->mailer = new TemplateBasedMailerSpy( $this );
	}

	public function testGivenValidRequest_applicationSucceeds() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidRequest() {
		return new ApplyForMembershipRequest();
	}

	private function newUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->repository,
			$this->authUpdater,
			$this->mailer
		);
	}

}
