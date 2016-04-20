<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;

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

	private function sendConfirmationEmail( MembershipApplication $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			$this->getConfirmationMailTemplateArguments( $application )
		);
	}

	private function getConfirmationMailTemplateArguments( MembershipApplication $application ): array {
		return [
			'applicationId' => $application->getId(),
			'salutation' => ( new GreetingGenerator() )->createGreeting(
				$application->getApplicant()->getPersonName()->getLastName(),
				$application->getApplicant()->getPersonName()->getSalutation(),
				$application->getApplicant()->getPersonName()->getTitle()
			)
		];
	}

}
