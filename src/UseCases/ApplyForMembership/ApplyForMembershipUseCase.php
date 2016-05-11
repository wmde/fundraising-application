<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCase {

	/* private */ const YEARLY_PAYMENT_MODERATION_THRESHOLD_IN_EURO = 1000;

	private $repository;
	private $authUpdater;
	private $mailer;
	private $tokenGenerator;
	private $validator;

	public function __construct( MembershipApplicationRepository $repository,
		MembershipAppAuthUpdater $authUpdater, TemplateBasedMailer $mailer,
		TokenGenerator $tokenGenerator, MembershipApplicationValidator $validator ) {

		$this->repository = $repository;
		$this->authUpdater = $authUpdater;
		$this->mailer = $mailer;
		$this->tokenGenerator = $tokenGenerator;
		$this->validator = $validator;
	}

	public function applyForMembership( ApplyForMembershipRequest $request ): ApplyForMembershipResponse {
		if ( !$this->validator->validate( $request )->isSuccessful() ) {
			// TODO: return failures (note that we have infrastructure failures that are not ConstraintViolations)
			return ApplyForMembershipResponse::newFailureResponse();
		}

		$application = $this->newApplicationFromRequest( $request );

		if ( $this->applicationNeedsModeration( $application ) ) {
			$application->markForModeration();
		}

		// TODO: handle exceptions
		$this->repository->storeApplication( $application );

		$accessToken = $this->tokenGenerator->generateToken();
		$updateToken = $this->tokenGenerator->generateToken();

		// TODO: handle exceptions
		$this->authUpdater->allowAccessViaToken( $application->getId(), $accessToken );
		$this->authUpdater->allowModificationViaToken( $application->getId(), $updateToken );

		// TODO: handle exceptions
		$this->sendConfirmationEmail( $application );

		return ApplyForMembershipResponse::newSuccessResponse( $accessToken, $updateToken, $application );
	}

	private function newApplicationFromRequest( ApplyForMembershipRequest $request ): MembershipApplication {
		return ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );
	}

	private function sendConfirmationEmail( MembershipApplication $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			[] // TODO
		);
	}

	private function applicationNeedsModeration( MembershipApplication $application ): bool {
		return
			$application->getPayment()->getYearlyAmount()->getEuroFloat()
			> self::YEARLY_PAYMENT_MODERATION_THRESHOLD_IN_EURO;
	}

}
