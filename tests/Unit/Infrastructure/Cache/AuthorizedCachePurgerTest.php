<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Cache;

use WMDE\Fundraising\Frontend\Infrastructure\Cache\CachePurger;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\CachePurgingException;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\AuthorizedCachePurger;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\Cache\AuthorizedCachePurger
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AuthorizedCachePurgerTest extends \PHPUnit\Framework\TestCase {

	const CORRECT_SECRET = 'correct secret';
	const WRONG_SECRET = 'wrong secret';

	public function testWhenSecretMatches_purgeHappens() {
		$cachePurger = $this->newCachePurger();
		$cachePurger->expects( $this->once() )->method( 'purgeCache' );

		$useCase = new AuthorizedCachePurger( $cachePurger, self::CORRECT_SECRET );

		$useCase->purgeCache( self::CORRECT_SECRET );
	}

	/**
	 * @return CachePurger|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newCachePurger() {
		return $this->createMock( CachePurger::class );
	}

	public function testWhenSecretDoesNotMatch_purgeDoesNotHappen() {
		$cachePurger = $this->newCachePurger();
		$cachePurger->expects( $this->never() )->method( 'purgeCache' );

		$useCase = new AuthorizedCachePurger( $cachePurger, self::CORRECT_SECRET );

		$useCase->purgeCache( self::WRONG_SECRET );
	}

	public function testWhenPurgeHappens_successIsReturned() {
		$useCase = new AuthorizedCachePurger( $this->newCachePurger(), self::CORRECT_SECRET );

		$this->assertSame(
			AuthorizedCachePurger::RESULT_SUCCESS,
			$useCase->purgeCache( self::CORRECT_SECRET )
		);
	}

	public function testWhenSecretDoesNotMatch_accessDeniedIsReturned() {
		$useCase = new AuthorizedCachePurger( $this->newCachePurger(), self::CORRECT_SECRET );

		$this->assertSame(
			AuthorizedCachePurger::RESULT_ACCESS_DENIED,
			$useCase->purgeCache( self::WRONG_SECRET )
		);
	}

	public function testWhenCachePurgeThrowsException_errorIsReturned() {
		$cachePurger = $this->newCachePurger();
		$cachePurger->expects( $this->any() )
			->method( 'purgeCache' )->willThrowException( new CachePurgingException( '' ) );

		$useCase = new AuthorizedCachePurger( $cachePurger, self::CORRECT_SECRET );

		$this->assertSame(
			AuthorizedCachePurger::RESULT_ERROR,
			$useCase->purgeCache( self::CORRECT_SECRET )
		);
	}

}
