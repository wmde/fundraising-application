<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\DonationRequest;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\PersonAddressValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenInvalidAddressType_exceptionIsThrown() {
		$repo = $this->getMock( DonationRepository::class );
		$personAddressValidator = $this->getMockBuilder( PersonAddressValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$this->setExpectedException( \InvalidArgumentException::class, 'Address type DUDE not supported' );

		( new AddDonationUseCase( $repo ) )->addDonation(
			$this->newInvalidAddressTypeRequest(), $personAddressValidator );
	}

	public function testGivenInvalidPaymentType_exceptionIsThrown() {
		$repo = $this->getMock( DonationRepository::class );
		$personAddressValidator = $this->getMockBuilder( PersonAddressValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$this->setExpectedException( \InvalidArgumentException::class, 'Payment type CASH not supported' );

		( new AddDonationUseCase( $repo ) )->addDonation(
			$this->newInvalidPaymentTypeRequest(), $personAddressValidator );
	}

	public function testValidationSucceeds_successResponseIsCreated() {
		$repo = $this->getMock( DonationRepository::class );
		$personAddressValidator = $this->getMockBuilder( PersonAddressValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$personAddressValidator->method( 'validate' )->willReturn( true );
		$personAddressValidator->method( 'getConstraintViolations' )->willReturn( [] );

		$useCase = new AddDonationUseCase( $repo );
		$result = $useCase->addDonation( $this->newMinimumDonationRequest(), $personAddressValidator );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testValidationFails_responseObjectContainsViolations() {
		$repo = $this->getMock( DonationRepository::class );

		$violation = new ConstraintViolation( 'foo', 'bar' );
		$personAddressValidator = $this->getFailingValidatorMock( $violation );

		$useCase = new AddDonationUseCase( $repo );
		$result = $useCase->addDonation( $this->newMinimumDonationRequest(), $personAddressValidator );
		$this->assertEquals( [ new ConstraintViolation( 'foo', 'bar' ) ], $result->getValidationErrors() );
	}

	private function getFailingValidatorMock( ConstraintViolation $violation ) {
		$personAddressValidator = $this->getMockBuilder( PersonAddressValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$personAddressValidator->method( 'validate' )->willReturn( false );
		$personAddressValidator->method( 'getConstraintViolations' )->willReturn( [ $violation ] );

		return $personAddressValidator;
	}

	private function newCompleteDonationRequest(): DonationRequest {
		$donationRequest = new DonationRequest();
		$donationRequest->setPaymentType( 'BEZ' );
		$donationRequest->setAddressType( 'person' );
		$donationRequest->setSalutation( 'Frau' );
		$donationRequest->setTitle( 'Dr.' );
		$donationRequest->setCompanyName( '' );
		$donationRequest->setFirstName( 'Elke' );
		$donationRequest->setLastName( 'Schmidt' );
		$donationRequest->setPostalAddress( 'Stiftstr. 50' );
		$donationRequest->setPostalCode( '20099' );
		$donationRequest->setCity( 'Hamburg' );
		$donationRequest->setCountry( 'DE' );
		$donationRequest->setEmailAddress( 'elke.schmidt@hotmail.com' );

		$donationRequest->setIban( 'DE12500105170648489890' );
		$donationRequest->setBic( 'INGDDEFFXXX' );
		$donationRequest->setBankAccount( '0648489890' );
		$donationRequest->setBankCode( '50010517' );
		$donationRequest->setBankName( 'ING-DiBa' );

		return $donationRequest;
	}

	private function newMinimumDonationRequest(): DonationRequest {
		$donationRequest = new DonationRequest();
		$donationRequest->setPaymentType( 'BEZ' );
		$donationRequest->setAddressType( 'person' );
		return $donationRequest;
	}

	private function newInvalidAddressTypeRequest(): DonationRequest {
		$donationRequest = new DonationRequest();
		$donationRequest->setPaymentType( 'BEZ' );
		$donationRequest->setAddressType( 'DUDE' );
		return $donationRequest;
	}

	private function newInvalidPaymentTypeRequest(): DonationRequest {
		$donationRequest = new DonationRequest();
		$donationRequest->setAddressType( 'person' );
		$donationRequest->setPaymentType( 'CASH' );
		return $donationRequest;
	}

}
