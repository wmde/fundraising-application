<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelMembershipApplicationUseCase {

	private $authorizer;
	private $repository;
	private $mailer;

	public function __construct( ApplicationAuthorizer $authorizer,
		ApplicationRepository $repository, TemplateBasedMailer $mailer ) {

		$this->authorizer = $authorizer;
		$this->repository = $repository;
		$this->mailer = $mailer;
	}

	public function cancelApplication( CancellationRequest $request ): CancellationResponse {
		if ( !$this->authorizer->canModifyApplication( $request->getApplicationId() ) ) {
			return $this->newFailureResponse( $request );
		}

		$application = $this->getApplicationById( $request->getApplicationId() );

		if ( $application === null ) {
			return $this->newFailureResponse( $request );
		}

		$application->cancel();

		try {
			$this->repository->storeApplication( $application );
		}
		catch ( StoreMembershipApplicationException $ex ) {
			// TODO: log?
			return $this->newFailureResponse( $request );
		}

		$this->sendConfirmationEmail( $application );

		return $this->newSuccessResponse( $request );
	}

	private function getApplicationById( int $id ) {
		try {
			return $this->repository->getApplicationById( $id );
		}
		catch ( GetMembershipApplicationException $ex ) {
			// TODO: log?
			return null;
		}
	}

	private function newFailureResponse( CancellationRequest $request ) {
		return new CancellationResponse( $request->getApplicationId(), CancellationResponse::IS_FAILURE );
	}

	private function newSuccessResponse( CancellationRequest $request ) {
		return new CancellationResponse( $request->getApplicationId(), CancellationResponse::IS_SUCCESS );
	}

	private function sendConfirmationEmail( Application $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			$this->getConfirmationMailTemplateArguments( $application )
		);
	}

	private function getConfirmationMailTemplateArguments( Application $application ): array {
		return [
			'applicationId' => $application->getId(),
			'membershipApplicant' => [
				'salutation' => $application->getApplicant()->getName()->getSalutation(),
				'title' => $application->getApplicant()->getName()->getTitle(),
				'lastName' => $application->getApplicant()->getName()->getLastName()
			]
		];
	}

}
