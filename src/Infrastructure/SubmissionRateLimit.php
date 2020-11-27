<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use DateInterval;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SubmissionRateLimit {

	private string $sessionKey;
	private DateInterval $intervalBetweenSubmissions;

	public function __construct( string $sessionKey, DateInterval $intervalBetweenSubmissions ) {
		$this->sessionKey = $sessionKey;
		$this->intervalBetweenSubmissions = $intervalBetweenSubmissions;
	}

	public function isSubmissionAllowed( SessionInterface $session ): bool {
		$lastSubmission = $session->get( $this->sessionKey, null );
		if ( $lastSubmission === null || !( $lastSubmission instanceof \DateTimeImmutable ) ) {
			return true;
		}

		$minNextTimestamp = $lastSubmission->add( $this->intervalBetweenSubmissions );
		if ( $minNextTimestamp > new \DateTime() ) {
			return false;
		}

		return true;
	}

	public function setRateLimitCookie( SessionInterface $session ) {
		$lastSubmission = $session->get( $this->sessionKey, null );
		if ( $lastSubmission === null || !( $lastSubmission instanceof \DateTimeImmutable ) ) {
			$session->set( $this->sessionKey, new \DateTimeImmutable() );
		}
	}

}
