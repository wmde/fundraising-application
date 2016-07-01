<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\PurgeCache;

use WMDE\Fundraising\Frontend\Infrastructure\CachePurger;
use WMDE\Fundraising\Frontend\UseCases\PurgeCache\PurgeCacheRequest;
use WMDE\Fundraising\Frontend\UseCases\PurgeCache\PurgeCacheUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\PurgeCache\PurgeCacheUseCase
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

}
