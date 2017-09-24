<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\SkinSettings;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\SkinSettings
 */
class SkinSettingsTest extends TestCase {

	public function testDefaultSkinIsSet(): void {
		$manager = new SkinSettings( ['a', 'b'], 'a', 500 );
		$this->assertSame( 'a', $manager->getDefaultSkin() );
	}

	public function testDefaultSkinGetsUsedOnConstruct(): void {
		$manager = new SkinSettings( ['a', 'c', 'b'], 'b', 500 );
		$this->assertSame( 'b', $manager->getSkin() );
	}

	public function testCookieLifetimeIsSet(): void {
		$manager = new SkinSettings( ['a', 'b'], 'a', 500 );
		$this->assertSame( 500, $manager->getCookieLifetime() );
	}

	public function testValidateSkin(): void {
		$manager = new SkinSettings( ['c', 'd'], 'd', 300 );
		$this->assertTrue( $manager->isValidSkin( 'c' ) );
		$this->assertFalse( $manager->isValidSkin( 'f' ) );
	}

	public function testSetSkinValid(): void {
		$manager = new SkinSettings( ['c', 'd'], 'd', 300 );
		$manager->setSkin( 'c' );
		$this->assertSame( 'c', $manager->getSkin() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'z' is not a valid skin name
	 */
	public function testSetSkinInvalid(): void {
		$manager = new SkinSettings( ['x', 'y'], 'y', 100 );
		$manager->setSkin( 'z' );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'init' is not a valid skin name
	 */
	public function testInvalidDefaultSkin(): void {
		new SkinSettings( ['f', 'g'], 'init', 700 );
	}

}
