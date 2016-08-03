<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\ApplicationTracker;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCase {

	/* private */ const YEARLY_PAYMENT_MODERATION_THRESHOLD_IN_EURO = 1000;

	private $repository;
	private $tokenFetcher;
	private $mailer;
	private $validator;
	private $piwikTracker;

	public function __construct( ApplicationRepository $repository,
		ApplicationTokenFetcher $tokenFetcher, TemplateBasedMailer $mailer,
		MembershipApplicationValidator $validator, ApplicationTracker $tracker,
		ApplicationPiwikTracker $piwikTracker ) {

		$this->repository = $repository;
		$this->tokenFetcher = $tokenFetcher;
		$this->mailer = $mailer;
		$this->validator = $validator;
		$this->membershipApplicationTracker = $tracker;
		$this->piwikTracker = $piwikTracker;
	}

	public function applyForMembership( ApplyForMembershipRequest $request ): ApplyForMembershipResponse {
		$validationResult = $this->validator->validate( $request );
		if ( !$validationResult->isSuccessful() ) {
			// TODO: return failures (note that we have infrastructure failures that are not ConstraintViolations)
			return ApplyForMembershipResponse::newFailureResponse( $validationResult );
		}

		$application = $this->newApplicationFromRequest( $request );

		if ( $this->applicationNeedsModeration( $application ) ) {
			$application->markForModeration();
		}

		// TODO: handle exceptions
		$this->repository->storeApplication( $application );

		// TODO: handle exceptions
		$this->membershipApplicationTracker->trackApplication( $application->getId(), $request->getTrackingInfo() );

		// TODO: handle exceptions
		$this->piwikTracker->trackApplication( $application->getId(), $request->getPiwikTrackingString() );

		// TODO: handle exceptions
		$this->sendConfirmationEmail( $application );

		// TODO: handle exceptions
		$tokens = $this->tokenFetcher->getTokens( $application->getId() );

		return ApplyForMembershipResponse::newSuccessResponse(
			$tokens->getAccessToken(),
			$tokens->getUpdateToken(),
			$application
		);
	}

	private function newApplicationFromRequest( ApplyForMembershipRequest $request ): Application {
		return ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );
	}

	private function sendConfirmationEmail( Application $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			[
				'membershipType' => $application->getType(),
				'membershipFee' => $application->getPayment()->getAmount()->getEuroString(),
				'paymentIntervalInMonths' => $application->getPayment()->getIntervalInMonths(),
				'salutation' => $application->getApplicant()->getName()->getSalutation(),
				'title' => $application->getApplicant()->getName()->getTitle(),
				'lastName' => $application->getApplicant()->getName()->getLastName()
			]
		);
	}

	private function applicationNeedsModeration( Application $application ): bool {
		return
			$application->getPayment()->getYearlyAmount()->getEuroFloat()
			> self::YEARLY_PAYMENT_MODERATION_THRESHOLD_IN_EURO;
	}

}
