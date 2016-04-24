<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCase {

	private $repository;
	private $authUpdater;
	private $mailer;

	public function __construct( MembershipApplicationRepository $repository,
		MembershipAppAuthUpdater $authUpdater, TemplateBasedMailer $mailer ) {

		$this->repository = $repository;
		$this->authUpdater = $authUpdater;
		$this->mailer = $mailer;
	}

	public function applyForMembership( ApplyForMembershipRequest $request ): ApplyForMembershipResponse {
		// TODO: validation
		// TODO: build domain object
		// TODO: persistence
		// TODO: update auth
		// TODO: confirmation email

		return ApplyForMembershipResponse::newSuccessResponse();
	}

}
