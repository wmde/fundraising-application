<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\Frontend\Infrastructure\SubmissionRateLimit;

#[CoversClass( SubmissionRateLimit::class )]
class SubmissionRateLimitTest extends TestCase {

	public function testSubmissionAllowedWhenNoPreviousSubmissionInSession(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );
		$session->expects( $this->once() )->method( 'get' )->willReturn( null );

		$this->assertTrue( $limit->isSubmissionAllowed( $session ) );
	}

	public function testSubmissionAllowedWhenSessionHasInvalidClass(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );
		$session->expects( $this->once() )->method( 'get' )->willReturn( 'Now is not the time!' );

		$this->assertTrue( $limit->isSubmissionAllowed( $session ) );
	}

	public function testSubmissionAllowedWhenSessionHasExpiredTimestamp(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );
		$session->expects( $this->once() )
			->method( 'get' )
			->willReturn(
				( new \DateTimeImmutable() )
					->sub( new \DateInterval( 'PT2H' ) )
			);

		$this->assertTrue( $limit->isSubmissionAllowed( $session ) );
	}

	public function testSubmissionForbiddenWhenSessionTimestampIsInRange(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );

		$session->expects( $this->once() )
			->method( 'get' )
			->willReturn(
				( new \DateTimeImmutable() )
					->sub( new \DateInterval( 'PT120S' ) )
			);

		$this->assertFalse( $limit->isSubmissionAllowed( $session ) );
	}

	public function testWhenSessionContainsNoTimestampItSetsTimestampToCurrent(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );
		$session->method( 'get' )->willReturn( null );
		$session = $this->createMock( SessionInterface::class );

		$session->expects( $this->once() )
			->method( 'set' )
			->with(
				'donation_timestamp',
				$this->callback( static function ( \DateTimeImmutable $date ) {
					$now = time();
					// use delta of 5 seconds to make this immune against slow tests
					return $now - $date->getTimestamp() < 5 && $now - $date->getTimestamp() >= 0;
				} )
			);

		$limit->setRateLimitCookie( $session );
	}

	public function testWhenSessionContainsTimestamp_itIsNotOverwritten(): void {
		$limit = new SubmissionRateLimit( 'donation_timestamp', new \DateInterval( 'PT1H' ) );
		$session = $this->createMock( SessionInterface::class );
		$session->method( 'get' )->willReturn( new \DateTimeImmutable() );

		$session->expects( $this->never() )->method( 'set' );

		$limit->setRateLimitCookie( $session );
	}

}
