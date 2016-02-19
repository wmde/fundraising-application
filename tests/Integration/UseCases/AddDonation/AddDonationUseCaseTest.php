<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testValidationSucceeds_successResponseIsCreated() {
		$donationValidator = $this->getMockBuilder( DonationValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$donationValidator->method( 'validate' )->willReturn( new ValidationResult() );

		$useCase = new AddDonationUseCase(
			$this->getMock( DonationRepository::class ),
			$donationValidator,
			new ReferrerGeneralizer( 'http://foo.bar', [] )
		);

		$this->assertTrue( $useCase->addDonation( $this->newMinimumDonationRequest() )->isSuccessful() );
	}

	public function testValidationFails_responseObjectContainsViolations() {
		$useCase = new AddDonationUseCase(
			$this->getMock( DonationRepository::class ),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			new ReferrerGeneralizer( 'http://foo.bar', [] )
		);

		$result = $useCase->addDonation( $this->newMinimumDonationRequest() );
		$this->assertEquals( [ new ConstraintViolation( 'foo', 'bar' ) ], $result->getValidationErrors() );
	}

	private function getFailingValidatorMock( ConstraintViolation $violation ): DonationValidator {
		$validator = $this->getMockBuilder( DonationValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'validate' )->willReturn( new ValidationResult( $violation ) );

		return $validator;
	}

	private function newMinimumDonationRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setPaymentType( PaymentType::DIRECT_DEBIT );
		return $donationRequest;
	}

}
