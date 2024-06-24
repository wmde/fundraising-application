<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\RandomTokenGenerator;

#[CoversClass( RandomTokenGenerator::class )]
class RandomTokenGeneratorTest extends TestCase {
	#[DataProvider( 'provideLengths' )]
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

	#[DataProvider( 'provideInvalidLengths' )]
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
