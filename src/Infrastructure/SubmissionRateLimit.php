<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use DateInterval;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SubmissionRateLimit {

	public function __construct(
		private readonly string $sessionKey,
		private readonly DateInterval $intervalBetweenSubmissions
	) {
	}

	public function isSubmissionAllowed( SessionInterface $session ): bool {
		$lastSubmission = $session->get( $this->sessionKey, null );
		if ( !( $lastSubmission instanceof \DateTimeImmutable ) ) {
			return true;
		}

		$minNextTimestamp = $lastSubmission->add( $this->intervalBetweenSubmissions );
		if ( $minNextTimestamp > new \DateTime() ) {
			return false;
		}

		return true;
	}

	public function setRateLimitCookie( SessionInterface $session ): void {
		$lastSubmission = $session->get( $this->sessionKey, null );
		if ( !( $lastSubmission instanceof \DateTimeImmutable ) ) {
			$session->set( $this->sessionKey, new \DateTimeImmutable() );
		}
	}

}
