<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\Token;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\Token
 */
class TokenTest extends TestCase {
	public function testStringRepresentationIsHexadecimal(): void {
		$token = new Token( random_bytes( 16 ) );

		$this->assertMatchesRegularExpression( '/^[0-9a-f]+$/', (string)$token );
		$this->assertSame( 32, strlen( (string)$token ) );
	}

	public function testRawBytesAreReturned(): void {
		$token = new Token( 'RANDOMLY_CHOSEN' );

		$this->assertSame( 'RANDOMLY_CHOSEN', $token->getRawBytes() );
	}

	public function testFromHexConvertsToBytes(): void {
		$token = Token::fromHex( '6E4F74536543724574' );

		$this->assertSame( 'nOtSeCrEt', $token->getRawBytes() );
	}

	public function testFromHexFailsWhenGivenNonHexadecimalString(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid token, was not a hexadecimal value' );

		Token::fromHex( 'not hexadecimal' );
	}

}
