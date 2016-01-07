<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\StringValueGuard;

/**
 * @covers WMDE\Fundraising\Frontend\StringValueGuard
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StringValueGuardTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoRestrictions_valueIsAllowed() {
		$this->assertTrue( ( new StringValueGuard() )->isAllowed( 'kittens' ) );
	}

	public function testGivenWhitelist_valueNotOnItIsDisallowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertFalse( $guard->isAllowed( 'unicorns' ) );
	}

	public function testGivenWhitelist_valueOnWhitelistIsAllowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertTrue( $guard->isAllowed( 'kittens' ) );
	}

	public function testGivenBlacklist_valueOnBlacklistIsDisallowed() {
		$guard = new StringValueGuard();
		$guard->setBlacklist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertFalse( $guard->isAllowed( 'kittens' ) );
	}

	public function testGivenBlacklist_valueNotOnBlacklistIsAllowed() {
		$guard = new StringValueGuard();
		$guard->setBlacklist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertTrue( $guard->isAllowed( 'unicorns' ) );
	}

	public function testBlacklistAndWhitelist_valueOnBothIsNotAllowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens', 'ponies' ] );
		$guard->setBlacklist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertFalse( $guard->isAllowed( 'kittens' ) );
	}

	public function testBlacklistAndWhitelist_valueOnNeitherIsNotAllowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens', 'ponies' ] );
		$guard->setBlacklist( [ 'cats', 'kittens', 'ponies' ] );
		$this->assertFalse( $guard->isAllowed( 'unicorns' ) );
	}

	public function testBlacklistAndWhitelist_valueOnlyOnBlacklistIsNotAllowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens' ] );
		$guard->setBlacklist( [ 'ponies', 'unicorns' ] );
		$this->assertFalse( $guard->isAllowed( 'unicorns' ) );
	}

	public function testBlacklistAndWhitelist_valueOnlyOnWhitelistIsAllowed() {
		$guard = new StringValueGuard();
		$guard->setWhitelist( [ 'cats', 'kittens' ] );
		$guard->setBlacklist( [ 'ponies', 'unicorns' ] );
		$this->assertTrue( $guard->isAllowed( 'kittens' ) );
	}

	public function testGivenNonString_setWhitelistThrowsException() {
		$guard = new StringValueGuard();

		$this->setExpectedException( \InvalidArgumentException::class );
		$guard->setWhitelist( [ 'cats', 1337, 'ponies' ] );
	}

	public function testGivenNonString_setBaclklistThrowsException() {
		$guard = new StringValueGuard();

		$this->setExpectedException( \InvalidArgumentException::class );
		$guard->setBlacklist( [ 'cats', 1337, 'ponies' ] );
	}

}
