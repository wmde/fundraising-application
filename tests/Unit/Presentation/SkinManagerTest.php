<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\SkinManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\SkinManager
 */
class SkinManagerTest extends TestCase {

	public function testDefaultSkinSet(): void {
		$manager = new SkinManager( ['a', 'b'], 'a', 500 );
		$this->assertSame( 'a', $manager->getDefaultSkin() );
	}

	public function testDefaultSkinUsed(): void {
		$manager = new SkinManager( ['a', 'b'], 'a', 500 );
		$this->assertSame( 'a', $manager->getSkin() );
	}

	public function testCookieLifetimeSet(): void {
		$manager = new SkinManager( ['a', 'b'], 'a', 500 );
		$this->assertSame( 500, $manager->getCookieLifetime() );
	}

	public function testValidateSkin(): void {
		$manager = new SkinManager( ['c', 'd'], 'd', 300 );
		$this->assertTrue( $manager->isValidSkin( 'c' ) );
		$this->assertFalse( $manager->isValidSkin( 'f' ) );
	}

	public function testSetSkinValid(): void {
		$manager = new SkinManager( ['c', 'd'], 'd', 300 );
		$manager->setSkin( 'c' );
		$this->assertSame( 'c', $manager->getSkin() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'z' is not a valid skin name
	 */
	public function testSetSkinInvalid(): void {
		$manager = new SkinManager( ['x', 'y'], 'y', 100 );
		$manager->setSkin( 'z' );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'init' is not a valid skin name
	 */
	public function testInvalidDefaultSkin(): void {
		new SkinManager( ['f', 'g'], 'init', 700 );
	}

}
