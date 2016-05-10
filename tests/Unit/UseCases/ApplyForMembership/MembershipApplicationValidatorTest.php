<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidRequest_validationSucceeds() {
		$response = $this->newValidator()->validate( $this->newValidRequest() );

		$this->assertEquals( new Result( [] ), $response );
		$this->assertEmpty( $response->getViolationSources() );
		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidator() {
		return new MembershipApplicationValidator();
	}

	private function newValidRequest(): ApplyForMembershipRequest {
		return ValidMembershipApplicationRequest::newValidRequest();
	}

	/**
	 * @dataProvider invalidAmountProvider
	 */
	public function testGivenInvalidAmount_validationFails( string $amount, string $expectedViolation ) {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( $amount );
		$request->setPaymentIntervalInMonths( 12 ); // TODO: test different intervals

		$response = $this->newValidator()->validate( $request );

		$this->assertFalse( $response->isSuccessful() );
		$this->assertContains( Result::SOURCE_PAYMENT_AMOUNT, $response->getViolationSources() );
		$this->assertSame( $expectedViolation, $response->getViolationType( Result::SOURCE_PAYMENT_AMOUNT ) );
	}

	public function invalidAmountProvider() {
		return [
			'too low' => [ '1.00', Result::VIOLATION_TOO_LOW ],
			'just too low' => [ '24.99', Result::VIOLATION_TOO_LOW ],
			'max too low' => [ '0', Result::VIOLATION_TOO_LOW ],
			'invalid: negative' => [ '-1.00', Result::VIOLATION_NOT_MONEY ],
			'invalid' => [ 'y u no btc', Result::VIOLATION_NOT_MONEY ],
		];
	}

}
