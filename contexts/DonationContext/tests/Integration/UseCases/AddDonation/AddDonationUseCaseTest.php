<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\AddDonation;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokens;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FixedDonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationPolicyValidator;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationUseCase;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationValidationResult;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationValidator;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation\AddDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCaseTest extends \PHPUnit\Framework\TestCase {

	private const UPDATE_TOKEN = 'a very nice token';
	private const ACCESS_TOKEN = 'kindly allow me access';

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
			$this->getSucceedingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
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
			$this->getSucceedingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$result = $useCase->addDonation( $this->newMinimumDonationRequest() );
		$this->assertEquals( [ new ConstraintViolation( 'foo', 'bar' ) ], $result->getValidationErrors() );
	}

	public function testValidationFails_responseObjectContainsRequestObject() {
		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			$this->getSucceedingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$request = $this->newInvalidDonationRequest();
		$useCase->addDonation( $request );
		$this->assertEquals( $this->newInvalidDonationRequest(), $request );
	}

	private function getSucceedingValidatorMock(): AddDonationValidator {
		$validator = $this->getMockBuilder( AddDonationValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'validate' )->willReturn( new AddDonationValidationResult() );

		return $validator;
	}

	private function getFailingValidatorMock( ConstraintViolation $violation ): AddDonationValidator {
		$validator = $this->getMockBuilder( AddDonationValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'validate' )->willReturn( new AddDonationValidationResult( $violation ) );

		return $validator;
	}

	private function getSucceedingPolicyValidatorMock(): AddDonationPolicyValidator {
		$validator = $this->getMockBuilder( AddDonationPolicyValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'needsModeration' )->willReturn( false );

		return $validator;
	}

	private function getFailingPolicyValidatorMock(): AddDonationPolicyValidator {
		$validator = $this->getMockBuilder( AddDonationPolicyValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'needsModeration' )->willReturn( true );

		return $validator;
	}

	private function getAutoDeletingPolicyValidatorMock(): AddDonationPolicyValidator {
		$validator = $this->getMockBuilder( AddDonationPolicyValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$validator->method( 'isAutoDeleted' )->willReturn( true );

		return $validator;
	}

	private function newMinimumDonationRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setAmount( Euro::newFromString( '1.00' ) );
		$donationRequest->setPaymentType( PaymentType::BANK_TRANSFER );
		$donationRequest->setDonorType( DonorName::PERSON_ANONYMOUS );
		return $donationRequest;
	}

	private function newInvalidDonationRequest(): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$donationRequest->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donationRequest->setAmount( Euro::newFromInt( 0 ) );
		$donationRequest->setDonorType( DonorName::PERSON_ANONYMOUS );
		return $donationRequest;
	}

	public function testGivenInvalidRequest_noConfirmationEmailIsSend() {
		$mailer = $this->newMailer();

		$mailer->expects( $this->never() )->method( $this->anything() );

		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			$this->getSucceedingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$mailer,
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$useCase->addDonation( $this->newMinimumDonationRequest() );
	}

	private function newTransferCodeGenerator(): TransferCodeGenerator {
		return $this->createMock( TransferCodeGenerator::class );
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

	public function testGivenValidRequestWithPolicyViolation_donationIsModerated() {
		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			$this->getFailingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$response = $useCase->addDonation( $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' ) );
		$this->assertTrue( $response->getDonation()->needsModeration() );
	}

	public function testGivenPolicyViolationForExternalPaymentDonation_donationIsNotModerated() {
		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			$this->getFailingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$request = $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' );
		$request->setPaymentType( 'PPL' );
		$response = $useCase->addDonation( $request );
		$this->assertFalse( $response->getDonation()->needsModeration() );
	}

	private function newUseCaseWithMailer( DonationConfirmationMailer $mailer ) {
		return new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			$this->getSucceedingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$mailer,
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);
	}

	private function newValidAddDonationRequestWithEmail( string $email ): AddDonationRequest {
		$request = $this->newMinimumDonationRequest();

		$request->setDonorType( DonorName::PERSON_PRIVATE );
		$request->setDonorFirstName( ValidDonation::DONOR_FIRST_NAME );
		$request->setDonorLastName( ValidDonation::DONOR_LAST_NAME );
		$request->setDonorCompany( '' );
		$request->setDonorSalutation( ValidDonation::DONOR_SALUTATION );
		$request->setDonorTitle( ValidDonation::DONOR_TITLE );
		$request->setDonorStreetAddress( ValidDonation::DONOR_STREET_ADDRESS );
		$request->setDonorCity( ValidDonation::DONOR_CITY );
		$request->setDonorPostalCode( ValidDonation::DONOR_POSTAL_CODE );
		$request->setDonorCountryCode( ValidDonation::DONOR_COUNTRY_CODE );
		$request->setDonorEmailAddress( $email );

		return $request;
	}

	private function newValidCompanyDonationRequest(): AddDonationRequest {
		$request = $this->newMinimumDonationRequest();

		$request->setDonorType( DonorName::PERSON_COMPANY );
		$request->setDonorFirstName( '' );
		$request->setDonorLastName( '' );
		$request->setDonorCompany( ValidDonation::DONOR_LAST_NAME );
		$request->setDonorSalutation( '' );
		$request->setDonorTitle( '' );
		$request->setDonorStreetAddress( ValidDonation::DONOR_STREET_ADDRESS );
		$request->setDonorCity( ValidDonation::DONOR_CITY );
		$request->setDonorPostalCode( ValidDonation::DONOR_POSTAL_CODE );
		$request->setDonorCountryCode( ValidDonation::DONOR_COUNTRY_CODE );
		$request->setDonorEmailAddress( ValidDonation::DONOR_EMAIL_ADDRESS );

		return $request;
	}

	public function testWhenAdditionWorks_successResponseContainsTokens() {
		$useCase = $this->newValidationSucceedingUseCase();

		$response = $useCase->addDonation( $this->newMinimumDonationRequest() );

		$this->assertSame( self::UPDATE_TOKEN, $response->getUpdateToken() );
		$this->assertSame( self::ACCESS_TOKEN, $response->getAccessToken() );
	}

	public function testWhenAddingCompanyDonation_salutationFieldIsSet() {
		$useCase = $this->newValidationSucceedingUseCase();

		$response = $useCase->addDonation( $this->newValidCompanyDonationRequest() );

		$this->assertSame( DonorName::COMPANY_SALUTATION, $response->getDonation()->getDonor()->getName()->getSalutation() );
	}

	public function testWhenEmailAddressIsBlacklisted_donationIsMarkedAsDeleted() {
		$repository = $this->newRepository();
		$useCase = new AddDonationUseCase(
			$repository,
			$this->getSucceedingValidatorMock(),
			$this->getAutoDeletingPolicyValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newTokenFetcher()
		);

		$useCase->addDonation( $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' ) );
		$this->assertSame( Donation::STATUS_CANCELLED, $repository->getDonationById( 1 )->getStatus() );
	}

}
