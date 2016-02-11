<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenInvalidPaymentType_exceptionIsThrown() {
		$useCase = new AddDonationUseCase(
			$this->getMock( DonationRepository::class ),
			$this->getMockBuilder( DonationValidator::class )
				->disableOriginalConstructor()
				->getMock()
		);

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Payment type CASH not supported' );

		$useCase->addDonation( $this->newInvalidPaymentTypeRequest() );
	}

	public function testValidationSucceeds_successResponseIsCreated() {
		$donationValidator = $this->getMockBuilder( DonationValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$donationValidator->method( 'validate' )->willReturn( true );
		$donationValidator->method( 'getConstraintViolations' )->willReturn( [] );

		$useCase = new AddDonationUseCase(
			$this->getMock( DonationRepository::class ),
			$donationValidator
		);

		$this->assertTrue( $useCase->addDonation( $this->newMinimumDonationRequest() )->isSuccessful() );
	}

	public function testValidationFails_responseObjectContainsViolations() {
		$useCase = new AddDonationUseCase(
			$this->getMock( DonationRepository::class ),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) )
		);

		$result = $useCase->addDonation( $this->newMinimumDonationRequest() );
		$this->assertEquals( [ new ConstraintViolation( 'foo', 'bar' ) ], $result->getValidationErrors() );
	}

	private function getFailingValidatorMock( ConstraintViolation $violation ): DonationValidator {
		$validator = $this->getMockBuilder( DonationValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'validate' )->willReturn( false );
		$validator->method( 'getConstraintViolations' )->willReturn( [ $violation ] );

		return $validator;
	}

	private function newMinimumDonationRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setPaymentType( 'BEZ' );
		return $donationRequest;
	}

	private function newInvalidPaymentTypeRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setPaymentType( 'CASH' );
		return $donationRequest;
	}

}
