<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Symfony\Component\HttpFoundation\Cookie;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder
 */
class CookieBuilderTest extends TestCase {

	public function testNewCookieUsingDefaults(): void {
		$builder = new CookieBuilder( 0, '/', null, true, true, false, null );
		$cookie = $builder->newCookie( 'a', 'b' );
		$this->assertInstanceOf( Cookie::class, $cookie );
		$this->assertSame( 'a', $cookie->getName() );
		$this->assertSame( 'b', $cookie->getValue() );
		$this->assertSame( 0, $cookie->getExpiresTime() );
		$this->assertSame( '/', $cookie->getPath() );
		$this->assertNull( $cookie->getDomain() );
		$this->assertTrue( $cookie->isHttpOnly() );
		$this->assertTrue( $cookie->isSecure() );
		$this->assertFalse( $cookie->isRaw() );
		$this->assertNull( $cookie->getSameSite() );
	}

	public function testNewCookieOverriding(): void {
		$builder = new CookieBuilder( 0, '/', null, true, true, false, null );
		$cookie = $builder->newCookie( 'a', 'b', 500, '/here', 'sub.domain.com', true, 'strict' );
		$this->assertInstanceOf( Cookie::class, $cookie );
		$this->assertSame( 'a', $cookie->getName() );
		$this->assertSame( 'b', $cookie->getValue() );
		$this->assertSame( 500, $cookie->getExpiresTime() );
		$this->assertSame( '/here', $cookie->getPath() );
		$this->assertSame( 'sub.domain.com', $cookie->getDomain() );
		$this->assertTrue( $cookie->isHttpOnly() );
		$this->assertTrue( $cookie->isSecure() );
		$this->assertTrue( $cookie->isRaw() );
		$this->assertSame( 'strict', $cookie->getSameSite() );
	}

}
