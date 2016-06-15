<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Frontend\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationTokenFetcher;
use WMDE\Fundraising\Frontend\Infrastructure\DonationTokens;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedDonationTokenFetcher;
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
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const UPDATE_TOKEN = 'a very nice token';
	const ACCESS_TOKEN = 'kindly allow me access';

	/**
	 * @var \DateTime
	 */
	private $oneHourInTheFuture;

	public function setUp() {
		$this->oneHourInTheFuture = ( new \DateTime() )->add( $this->newOneHourInterval() );
	}

	public function testWhenValidationSucceeds_successResponseIsCreated() {
		$useCase = $this->newValidationSucceedingUseCase();

		$this->assertTrue( $useCase->addDonation( $this->newMinimumDonationRequest() )->isSuccessful() );
	}

	private function newValidationSucceedingUseCase(): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenFetcher()
		);
	}

	/**
	 * @return DonationConfirmationMailer|PHPUnit_Framework_MockObject_MockObject
	 */
	private function newMailer(): DonationConfirmationMailer {
		return $this->getMockBuilder( DonationConfirmationMailer::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newTokenFetcher(): DonationTokenFetcher {
		return new FixedDonationTokenFetcher( new DonationTokens(
			self::ACCESS_TOKEN,
			self::UPDATE_TOKEN
		) );
	}

	private function newOneHourInterval(): \DateInterval {
		return new \DateInterval( 'PT1H' );
	}

	private function newRepository(): DonationRepository {
		return new FakeDonationRepository();
	}

	public function testValidationFails_responseObjectContainsViolations() {
		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenFetcher()
		);

		$result = $useCase->addDonation( $this->newMinimumDonationRequest() );
		$this->assertEquals( [ new ConstraintViolation( 'foo', 'bar' ) ], $result->getValidationErrors() );
	}

	public function testValidationFails_responseObjectContainsRequestObject() {
		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenFetcher()
		);

		$request = $this->newInvalidDonationRequest();
		$useCase->addDonation( $request );
		$this->assertEquals( $this->newInvalidDonationRequest(), $request );
	}

	private function getSucceedingValidatorMock(): DonationValidator {
		$validator = $this->getMockBuilder( DonationValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'validate' )->willReturn( new ValidationResult() );

		return $validator;
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
		$donationRequest->setAmount( Euro::newFromString( '1.00' ) );
		$donationRequest->setPaymentType( PaymentType::DIRECT_DEBIT );
		return $donationRequest;
	}

	private function newInvalidDonationRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donationRequest->setAmount( Euro::newFromInt( 0 ) );
		return $donationRequest;
	}

	public function testGivenInvalidRequest_noConfirmationEmailIsSend() {
		$mailer = $this->newMailer();

		$mailer->expects( $this->never() )->method( $this->anything() );

		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$mailer,
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenFetcher()
		);

		$useCase->addDonation( $this->newMinimumDonationRequest() );
	}

	private function newTransferCodeGenerator(): TransferCodeGenerator {
		return $this->createMock( TransferCodeGenerator::class );
	}

	private function newBankDataConverter(): BankDataConverter {
		return $this->getMockBuilder( BankDataConverter::class )->disableOriginalConstructor()->getMock();
	}

	public function testGivenValidRequest_confirmationEmailIsSent() {
		$mailer = $this->newMailer();
		$donation = $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' );

		$mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->isInstanceOf( Donation::class ) );

		$useCase = $this->newUseCaseWithMailer( $mailer );

		$useCase->addDonation( $donation );
	}

	public function testGivenValidRequestWithExternalPaymentType_confirmationEmailIsNotSent() {
		$mailer = $this->newMailer();

		$mailer->expects( $this->never() )->method( $this->anything() );

		$useCase = $this->newUseCaseWithMailer( $mailer );

		$request = $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' );
		$request->setPaymentType( 'PPL' );
		$useCase->addDonation( $request );
	}

	private function newUseCaseWithMailer( DonationConfirmationMailer $mailer ) {
		return new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$mailer,
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenFetcher()
		);
	}

	private function newValidAddDonationRequestWithEmail( string $email ): AddDonationRequest {
		$request = $this->newMinimumDonationRequest();

		$request->setPersonalInfo( new Donor(
			PersonName::newPrivatePersonName(),
			new PhysicalAddress(),
			$email
		) );

		return $request;
	}

	public function testWhenAdditionWorks_successResponseContainsTokens() {
		$useCase = $this->newValidationSucceedingUseCase();

		$response = $useCase->addDonation( $this->newMinimumDonationRequest() );

		$this->assertSame( self::UPDATE_TOKEN, $response->getUpdateToken() );
		$this->assertSame( self::ACCESS_TOKEN, $response->getAccessToken() );
	}

}
