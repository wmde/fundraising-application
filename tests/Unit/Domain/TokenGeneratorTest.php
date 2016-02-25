<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TokenGeneratorTest extends \PHPUnit_Framework_TestCase {

	const ADD_SECRET = true;

	private $secret = 'veryverysecret';

	public function testAccessTokenGeneration() {
		$this->assertSame(
			'1234567$65ffd19c12aee64791e4bd1feffbcecff51d6c74',
			$this->generateAccessToken( 123456, '2015-02-25 19:04:22', '1234567' )
		);
	}

	public function testUpdateTokenGeneration() {
		$this->assertSame(
			'282da8d895f0c98a9dfe5538036b64596fdf62bf',
			$this->generateUpdateToken( 123456, '2015-02-25 19:04:22', 1234567 )
		);
	}

	private function generateAccessToken( int $id, string $timestamp, $salt = '' ) {
		if ( $salt === '' ) {
			$salt = random_int( 1000000, 9999999 );
		}

		return $salt . '$' . $this->generateHash( $this->composeToken( [ $salt, $id, $timestamp ] ) );
	}

	private function generateUpdateToken( int $random, string $datetime, $remoteAddress ) {
		return $this->generateHash( $this->composeToken( [ $random, $datetime, $remoteAddress ] ) );
	}

	private function generateHash( string $value, bool $addSecret = false ) {
		if ( $addSecret ) {
			$value .= '+' . $this->secret;
		}

		return sha1( $value );
	}

	private function composeToken( array $values, string $glue = '|' ) {
		$parts = [];
		foreach ( $values as $value ) {
			$parts[] = $this->generateHash( $value, self::ADD_SECRET );
		}

		return implode( $glue, $parts );
	}

}
