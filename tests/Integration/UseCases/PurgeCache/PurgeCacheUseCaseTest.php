<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\PurgeCache;

use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachePurger;
use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache\PurgeCacheRequest;
use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache\PurgeCacheResponse;
use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache\PurgeCacheUseCase;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachePurgingException;

/**
 * @covers WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache\PurgeCacheUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PurgeCacheUseCaseTest extends \PHPUnit_Framework_TestCase {

	const CORRECT_SECRET = 'correct secret';
	const WRONG_SECRET = 'wrong secret';

	public function testWhenSecretMatches_purgeHappens() {
		$cachePurger = $this->newCachePurger();
		$cachePurger->expects( $this->once() )->method( 'purgeCache' );

		$useCase = new PurgeCacheUseCase( $cachePurger, self::CORRECT_SECRET );

		$useCase->purgeCache( new PurgeCacheRequest( self::CORRECT_SECRET ) );
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

		$useCase = new PurgeCacheUseCase( $cachePurger, self::CORRECT_SECRET );

		$useCase->purgeCache( new PurgeCacheRequest( self::WRONG_SECRET ) );
	}

	public function testWhenPurgeHappens_successIsReturned() {
		$useCase = new PurgeCacheUseCase( $this->newCachePurger(), self::CORRECT_SECRET );

		$response = $useCase->purgeCache( new PurgeCacheRequest( self::CORRECT_SECRET ) );

		$this->assertSame( PurgeCacheResponse::SUCCESS, $response->getState() );
	}

	public function testWhenSecretDoesNotMatch_accessDeniedIsReturned() {
		$useCase = new PurgeCacheUseCase( $this->newCachePurger(), self::CORRECT_SECRET );

		$response = $useCase->purgeCache( new PurgeCacheRequest( self::WRONG_SECRET ) );

		$this->assertSame( PurgeCacheResponse::ACCESS_DENIED, $response->getState() );
	}

	public function testWhenCachePurgeThrowsException_errorIsReturned() {
		$cachePurger = $this->newCachePurger();
		$cachePurger->expects( $this->any() )
			->method( 'purgeCache' )->willThrowException( new CachePurgingException( '' ) );

		$useCase = new PurgeCacheUseCase( $cachePurger, self::CORRECT_SECRET );

		$response = $useCase->purgeCache( new PurgeCacheRequest( self::CORRECT_SECRET ) );

		$this->assertSame( PurgeCacheResponse::ERROR, $response->getState() );
	}

}
