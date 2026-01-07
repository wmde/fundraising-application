<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\GetInTouch;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\OperatorMailer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\FunValidators\ValidationResult;

#[CoversClass( GetInTouchUseCase::class )]
class GetInTouchUseCaseTest extends TestCase {

	private const INQUIRER_FIRST_NAME = 'Curious';
	private const INQUIRER_LAST_NAME = 'Guy';
	private const INQUIRER_EMAIL_ADDRESS = 'curious.guy@alltheguys.com';
	private const INQUIRER_DONATION_NUMBER = '123456';
	private const INQUIRY_SUBJECT = 'Please let me know';
	private const INQUIRY_CATEGORY = 'Other';
	private const INQUIRY_MESSAGE = 'What is it you do?';

	private GetInTouchValidator $validator;
	private OperatorMailer $operatorMailer;

	private TemplateBasedMailerSpy $userMailer;

	public function setUp(): void {
		$this->validator = $this->newSucceedingValidator();
		$this->userMailer = new TemplateBasedMailerSpy( $this );
	}

	private function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->validator,
			$this->operatorMailer,
			$this->userMailer
		);
	}

	public function testGivenValidParameters_theyAreContainedInTheEmailToOperator(): void {
		$this->operatorMailer = $this->createMock( OperatorMailer::class );
		$this->operatorMailer->expects( $this->once() )
			->method( 'sendMailToOperator' )
			->with(
				new EmailAddress( self::INQUIRER_EMAIL_ADDRESS ),
				self::INQUIRY_SUBJECT,
				$this->equalTo( [
					'firstName' => self::INQUIRER_FIRST_NAME,
					'lastName' => self::INQUIRER_LAST_NAME,
					'emailAddress' => self::INQUIRER_EMAIL_ADDRESS,
					'donationNumber' => self::INQUIRER_DONATION_NUMBER,
					'subject' => self::INQUIRY_SUBJECT,
					'category' => self::INQUIRY_CATEGORY,
					'message' => self::INQUIRY_MESSAGE
				] )
			);

		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $this->newRequest() );
	}

	public function testGivenValidRequest_theUserIsNotified(): void {
		$this->operatorMailer = $this->createStub( OperatorMailer::class );
		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $this->newRequest() );

		$this->userMailer->assertCalledOnce();
		$this->userMailer->assertCalledOnceWith(
			new EmailAddress( self::INQUIRER_EMAIL_ADDRESS ),
			[]
		);
	}

	private function newRequest(): GetInTouchRequest {
		return new GetInTouchRequest(
			self::INQUIRER_FIRST_NAME,
			self::INQUIRER_LAST_NAME,
			self::INQUIRER_EMAIL_ADDRESS,
			self::INQUIRER_DONATION_NUMBER,
			self::INQUIRY_SUBJECT,
			self::INQUIRY_CATEGORY,
			self::INQUIRY_MESSAGE
		);
	}

	/**
	 * @return GetInTouchValidator&Stub
	 */
	private function newSucceedingValidator(): GetInTouchValidator {
		$validator = $this->createStub( GetInTouchValidator::class );
		$validator->method( 'validate' )->willReturn( new ValidationResult() );

		return $validator;
	}

}
