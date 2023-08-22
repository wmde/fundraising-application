<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\RandomTokenGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\RandomTokenGenerator
 */
class RandomTokenGeneratorTest extends TestCase {
	/**
	 * @dataProvider provideLengths
	 */
	public function testGenerateTokensWithTheRightLength( int $length ): void {
		 $generator = new RandomTokenGenerator( $length );

		 $token = $generator->generateToken();

		 $this->assertSame( $length, strlen( $token->getRawBytes() ) );
	}

	/**
	 * @return iterable<array{int}>
	 */
	public static function provideLengths(): iterable {
		yield [ 16 ];
		yield [ 24 ];
		yield [ 32 ];
		yield [ 64 ];
	}

	/**
	 * @dataProvider provideInvalidLengths
	 */
	public function testTokenGeneratorHasMinimumLength( int $invalidLength ): void {
		$this->expectException( \InvalidArgumentException::class );
		new RandomTokenGenerator( $invalidLength );
	}

	/**
	 * @return iterable<array{int}>
	 */
	public static function provideInvalidLengths(): iterable {
		yield 'length 0' => [ 0 ];
		yield 'minimum length is 16' => [ 15 ];
	}

}
