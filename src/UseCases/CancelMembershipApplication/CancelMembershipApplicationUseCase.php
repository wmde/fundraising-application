<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelMembershipApplicationUseCase {

	private $authorizer;
	private $repository;
	private $mailer;

	public function __construct( MembershipApplicationAuthorizer $authorizer,
		MembershipApplicationRepository $repository, TemplateBasedMailer $mailer ) {

		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->mailer = $mailer;
	}

	public function cancelApplication( CancellationRequest $request ): CancellationResponse {
		// TODO
	}

}
