<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmissionRateLimit {

	public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	private string $cookieName;
	private LoggerInterface $logger;
	private DateInterval $intervalBetweenSubmissions;
	private CookieBuilder $cookieBuilder;

	public function __construct( string $cookieName, DateInterval $intervalBetweenSubmissions, CookieBuilder $cookieBuilder, LoggerInterface $logger ) {
		$this->cookieName = $cookieName;
		$this->logger = $logger;
		$this->intervalBetweenSubmissions = $intervalBetweenSubmissions;
		$this->cookieBuilder = $cookieBuilder;
	}

	public function isSubmissionAllowed( Request $request ): bool {
		$lastSubmission = $request->cookies->get( $this->cookieName, '' );
		if ( $lastSubmission === '' ) {
			return true;
		}

		$timeFromCookie = \DateTime::createFromFormat( self::TIMESTAMP_FORMAT, $lastSubmission );
		if ( $timeFromCookie === false ) {
			$this->logger->info( sprintf(
				'Invalid time string in cookie "%s": "%s" does not match "%s"',
				$this->cookieName,
				$lastSubmission,
				self::TIMESTAMP_FORMAT
			) );
			return true;
		}

		$minNextTimestamp = $timeFromCookie->add( $this->intervalBetweenSubmissions );
		if ( $minNextTimestamp > new \DateTime() ) {
			return false;
		}

		return true;
	}

	public function setRateLimitCookie( Request $request, Response $response ) {
		if ( !$request->cookies->get( $this->cookieName ) ) {
			$response->headers->setCookie(
				$this->cookieBuilder->newCookie( $this->cookieName, date( self::TIMESTAMP_FORMAT ) )
			);
		}
	}

}
