<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\DonationContext\UseCases\AddDonation;

use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationUseCase;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationUseCase
 */
class AddDonationUseCaseTest extends TestCase {

	public function testGetInitialDonationStatus(): void {
		$useCase = $this->createMock( AddDonationUseCase::class );
		$method = new ReflectionMethod( $useCase, 'getInitialDonationStatus' );
		$method->setAccessible( true );

		$this->assertSame( 'N', $method->invoke( $useCase, 'BEZ' ) );
		$this->assertSame( 'Z', $method->invoke( $useCase, 'UEB' ) );
		$this->assertSame( 'Z', $method->invoke( $useCase, 'SUB' ) );

		$this->assertSame( 'X', $method->invoke( $useCase, 'MCP' ) );
		$this->assertSame( 'X', $method->invoke( $useCase, 'foo' ) );
	}
}
