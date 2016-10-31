<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplicationRequest;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipFeeValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidRequest_validationSucceeds() {
		$validRequest = $this->newValidRequest();
		$response = $this->newValidator()->validate(
			$validRequest->getPaymentAmountInEuros(),
			$validRequest->getPaymentIntervalInMonths(),
			$validRequest->isCompanyApplication() ? 'firma' : 'person'
		);

		$this->assertEquals( new Result( [] ), $response );
		$this->assertEmpty( $response->getViolationSources() );
		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidator() {
		return new MembershipFeeValidator();
	}

	private function newValidRequest(): ApplyForMembershipRequest {
		return ValidMembershipApplicationRequest::newValidRequest();
	}

	/**
	 * @dataProvider invalidAmountProvider
	 */
	public function testGivenInvalidAmount_validationFails( string $amount, int $intervalInMonths, string $expectedViolation ) {
		$request = $this->newValidRequestWithPaymentAmount( $amount, $intervalInMonths );

		$response = $this->newValidator()->validate(
			$amount,
			$intervalInMonths,
			$request->isCompanyApplication() ? 'firma' : 'person'
		);

		$this->assertFalse( $response->isSuccessful() );
		$this->assertContains( Result::SOURCE_PAYMENT_AMOUNT, $response->getViolationSources() );
		$this->assertSame( $expectedViolation, $response->getViolationType( Result::SOURCE_PAYMENT_AMOUNT ) );
	}

	public function invalidAmountProvider() {
		return [
			'invalid: negative' => [ '-1.00', 3, Result::VIOLATION_NOT_MONEY ],
			'invalid' => [ 'y u no btc', 3, Result::VIOLATION_NOT_MONEY ],

			'too low single payment' => [ '1.00', 12, Result::VIOLATION_TOO_LOW ],
			'just too low single payment' => [ '23.99', 12, Result::VIOLATION_TOO_LOW ],
			'max too low single payment' => [ '0', 12, Result::VIOLATION_TOO_LOW ],

			'too low 12 times' => [ '1.99', 1, Result::VIOLATION_TOO_LOW ],
			'too low 4 times' => [ '5.99', 3, Result::VIOLATION_TOO_LOW ],
		];
	}

	private function newValidRequestWithPaymentAmount( string $amount, int $intervalInMonths ) {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( $amount );
		$request->setPaymentIntervalInMonths( $intervalInMonths );
		return $request;
	}

	/**
	 * @dataProvider validAmountProvider
	 */
	public function testGivenValidAmount_validationSucceeds( string $amount, int $intervalInMonths ) {
		$request = $this->newValidRequestWithPaymentAmount( $amount, $intervalInMonths );

		$this->assertTrue(
			$this->newValidator()
				->validate( $amount, $intervalInMonths, $this->getRequestAddressType( $request ) )
				->isSuccessful()
		);
	}

	public function validAmountProvider() {
		return [
			'single payment' => [ '50.00', 12 ],
			'just enough single payment' => [ '24.00', 12 ],
			'high single payment' => [ '31333.37', 12 ],

			'just enough 12 times' => [ '2.00', 1 ],
			'just enough 4 times' => [ '6.00', 3 ],
		];
	}

	public function testGivenValidCompanyAmount_validationSucceeds() {
		$request = $this->newValidRequestWithPaymentAmount( '100.00', 12 );
		$request->markApplicantAsCompany();

		$this->assertTrue(
			$this->newValidator()
				->validate( '100.00', 12, $this->getRequestAddressType( $request ) )
				->isSuccessful()
		);
	}

	public function testGivenInvalidCompanyAmount_validationFails() {
		$request = $this->newValidRequestWithPaymentAmount( '99.99', 12 );
		$request->markApplicantAsCompany();

		$this->assertFalse(
			$this->newValidator()
				->validate( '99.99', 12, $this->getRequestAddressType( $request ) )
				->isSuccessful()
		);
	}

	private function getRequestAddressType( ApplyForMembershipRequest $request ) {
		return $request->isCompanyApplication() ? 'firma' : 'person';
	}

}
