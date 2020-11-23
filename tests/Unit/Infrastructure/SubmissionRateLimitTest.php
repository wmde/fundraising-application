<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;
use WMDE\Fundraising\Frontend\Infrastructure\SubmissionRateLimit;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\SubmissionRateLimit
 */
class SubmissionRateLimitTest extends TestCase {

	public function testSubmissionAllowedWhenCookieIsEmpty(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$request = new Request();

		$this->assertTrue( $limit->isSubmissionAllowed( $request ) );
	}

	public function testSubmissionAllowedWhenCookieHasInvalidTimestamp(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$request = new Request( [], [], [], [ 'donation_timestamp' => 'Now is not the time!' ] );

		$this->assertTrue( $limit->isSubmissionAllowed( $request ) );
	}

	public function testSubmissionAllowedWhenCookieHasExpiredTimestamp(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$request = new Request( [], [], [], [ 'donation_timestamp' => date( SubmissionRateLimit::TIMESTAMP_FORMAT, time() - 7200 ) ] );

		$this->assertTrue( $limit->isSubmissionAllowed( $request ) );
	}

	public function testSubmissionForbiddenWhenCookieTimestampIsInRange(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$request = new Request( [], [], [], [ 'donation_timestamp' => date( SubmissionRateLimit::TIMESTAMP_FORMAT, time() - 120 ) ] );

		$this->assertFalse( $limit->isSubmissionAllowed( $request ) );
	}

	public function testWhenTimestampOfPreviousDonationIsFaulty_timestampGetsLogged(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$logger = new LoggerSpy();
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, $logger );
		$request = new Request( [], [], [], [ 'donation_timestamp' => 'Now is not the time!' ] );

		$limit->isSubmissionAllowed( $request );

		$call = $logger->getFirstLogCall();
		$this->assertSame( LogLevel::INFO, $call->getLevel() );
		$this->assertStringContainsString( 'Now is not the time', $call->getMessage() );
	}

	public function testWhenRequestHasNoCookieItSetsRateLimitCookieWithCurrentDateValue(): void {
		$cookieBuilder = new CookieBuilder( time() + 10000, '/', 'example.com', true, false, false, null );
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$response = new Response();
		$request = new Request();

		$limit->setRateLimitCookie( $request, $response );

		$limitCookie = $response->headers->getCookies()[0];
		$this->assertSame( 'donation_timestamp', $limitCookie->getName() );
		$this->assertStringStartsWith( date( SubmissionRateLimit::TIMESTAMP_FORMAT, time() ), $limitCookie->getValue() );
	}

	public function testWhenDonationTimestampCookieExists_itIsNotOverwritten(): void {
		$cookieBuilder = $this->createMock( CookieBuilder::class );
		$cookieBuilder->expects( $this->never() )->method( 'newCookie' );

		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ), $cookieBuilder, new NullLogger() );
		$response = new Response();
		$request = new Request( [], [], [], [ 'donation_timestamp' => '2020-11-11 11:11:00' ] );

		$limit->setRateLimitCookie( $request, $response );
	}

}
