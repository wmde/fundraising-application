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

		$this->assertEquals( 'N', $method->invoke( $useCase, 'BEZ' ) );
		$this->assertEquals( 'Z', $method->invoke( $useCase, 'UEB' ) );
		$this->assertEquals( 'Z', $method->invoke( $useCase, 'SUB' ) );

		$this->assertEquals( 'X', $method->invoke( $useCase, 'MCP' ) );
		$this->assertEquals( 'X', $method->invoke( $useCase, 'foo' ) );
	}
}
