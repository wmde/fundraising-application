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
		$settings = new SkinSettings( ['a', 'b'], 'a', 500 );
		$this->assertSame( 'a', $settings->getDefaultSkin() );
	}

	public function testDefaultSkinGetsUsedOnConstruct(): void {
		$settings = new SkinSettings( ['a', 'c', 'b'], 'b', 500 );
		$this->assertSame( 'b', $settings->getSkin() );
	}

	public function testCookieLifetimeIsSet(): void {
		$settings = new SkinSettings( ['a', 'b'], 'a', 500 );
		$this->assertSame( 500, $settings->getCookieLifetime() );
	}

	public function testValidateSkin(): void {
		$settings = new SkinSettings( ['c', 'd'], 'd', 300 );
		$this->assertTrue( $settings->isValidSkin( 'c' ) );
		$this->assertFalse( $settings->isValidSkin( 'f' ) );
	}

	public function testSetSkinValid(): void {
		$settings = new SkinSettings( ['c', 'd'], 'd', 300 );
		$settings->setSkin( 'c' );
		$this->assertSame( 'c', $settings->getSkin() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'z' is not a valid skin name
	 */
	public function testSetSkinInvalid(): void {
		$settings = new SkinSettings( ['x', 'y'], 'y', 100 );
		$settings->setSkin( 'z' );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage 'init' is not a valid skin name
	 */
	public function testInvalidDefaultSkin(): void {
		new SkinSettings( ['f', 'g'], 'init', 700 );
	}

}
