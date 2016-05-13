<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddDonation;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Frontend\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
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
			$this->newTokenGenerator(),
			$this->newAuthorizationUpdater()
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

	private function newTokenGenerator(): TokenGenerator {
		return new FixedTokenGenerator(
			self::UPDATE_TOKEN,
			$this->oneHourInTheFuture
		);
	}

	/**
	 * @return DonationAuthorizationUpdater|PHPUnit_Framework_MockObject_MockObject
	 */
	private function newAuthorizationUpdater(): DonationAuthorizationUpdater {
		return $this->getMock( DonationAuthorizationUpdater::class );
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
			$this->newTokenGenerator(),
			$this->newAuthorizationUpdater()
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
			$this->newTokenGenerator(),
			$this->newAuthorizationUpdater()
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

		$mailer->expects( $this->never() )->method( 'sendMailFromDonation' );

		$useCase = new AddDonationUseCase(
			$this->newRepository(),
			$this->getFailingValidatorMock( new ConstraintViolation( 'foo', 'bar' ) ),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$mailer,
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenGenerator(),
			$this->newAuthorizationUpdater()
		);

		$useCase->addDonation( $this->newMinimumDonationRequest() );
	}

	private function newTransferCodeGenerator(): TransferCodeGenerator {
		return $this->getMock( TransferCodeGenerator::class );
	}

	private function newBankDataConverter(): BankDataConverter {
		return $this->getMockBuilder( BankDataConverter::class )->disableOriginalConstructor()->getMock();
	}

	public function testGivenValidRequest_confirmationEmailIsSent() {
		$mailer = $this->newMailer();
		$donation = $this->newValidAddDonationRequestWithEmail( 'foo@bar.baz' );

		$mailer->expects( $this->once() )
			->method( 'sendMailFromDonation' )
			->with( $this->isInstanceOf( Donation::class ) );

		$useCase = $this->newUseCaseWithMailer( $mailer );

		$useCase->addDonation( $donation );
	}

	public function testGivenValidRequestWithExternalPaymentType_confirmationEmailIsNotSent() {
		$mailer = $this->newMailer();

		$mailer->expects( $this->never() )->method( 'sendMailFromDonation' );

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
			$this->newTokenGenerator(),
			$this->newAuthorizationUpdater()
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
		$this->assertSame( self::UPDATE_TOKEN, $response->getAccessToken() );
	}

	public function testWhenAdditionWorks_updateTokenIsPersisted() {
		$authorizationUpdater = $this->newAuthorizationUpdater();

		$authorizationUpdater->expects( $this->once() )
			->method( 'allowModificationViaToken' )
			->with(
				$this->equalTo( 1 ),
				$this->equalTo( self::UPDATE_TOKEN ),
				$this->equalTo( $this->oneHourInTheFuture )
			);

		$useCase = $this->newUseCaseWithAuthorizationUpdater( $authorizationUpdater );

		$useCase->addDonation( $this->newMinimumDonationRequest() );
	}

	public function testWhenAdditionWorks_accessTokenIsPersisted() {
		$authorizationUpdater = $this->newAuthorizationUpdater();

		$authorizationUpdater->expects( $this->once() )
			->method( 'allowAccessViaToken' )
			->with(
				$this->equalTo( 1 ),
				$this->equalTo( self::UPDATE_TOKEN )
			);

		$useCase = $this->newUseCaseWithAuthorizationUpdater( $authorizationUpdater );

		$useCase->addDonation( $this->newMinimumDonationRequest() );
	}

	private function newUseCaseWithAuthorizationUpdater( DonationAuthorizationUpdater $authUpdater ): AddDonationUseCase {
		return new AddDonationUseCase(
			$this->newRepository(),
			$this->getSucceedingValidatorMock(),
			new ReferrerGeneralizer( 'http://foo.bar', [] ),
			$this->newMailer(),
			$this->newTransferCodeGenerator(),
			$this->newBankDataConverter(),
			$this->newTokenGenerator(),
			$authUpdater
		);
	}

	public function testWhenUpdateAuthorizationUpdateFails_failureResponseIsReturned() {
		$authorizationUpdater = $this->newAuthorizationUpdater();

		$authorizationUpdater->expects( $this->any() )
			->method( 'allowModificationViaToken' )
			->willThrowException( new AuthorizationUpdateException( 'Auth update failed' ) );

		$useCase = $this->newUseCaseWithAuthorizationUpdater( $authorizationUpdater );

		$response = $useCase->addDonation( $this->newMinimumDonationRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testWhenAccessAuthorizationUpdateFails_failureResponseIsReturned() {
		$authorizationUpdater = $this->newAuthorizationUpdater();

		$authorizationUpdater->expects( $this->any() )
			->method( 'allowAccessViaToken' )
			->willThrowException( new AuthorizationUpdateException( 'Auth update failed' ) );

		$useCase = $this->newUseCaseWithAuthorizationUpdater( $authorizationUpdater );

		$response = $useCase->addDonation( $this->newMinimumDonationRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

}
