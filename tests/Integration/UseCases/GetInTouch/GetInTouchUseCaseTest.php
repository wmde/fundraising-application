<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Infrastructure\OperatorMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;
use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCaseTest extends \PHPUnit\Framework\TestCase {

	const INQUIRER_FIRST_NAME = 'Curious';
	const INQUIRER_LAST_NAME = 'Guy';
	const INQUIRER_EMAIL_ADDRESS = 'curious.guy@alltheguys.com';
	const INQUIRY_SUBJECT = 'Please let me know';
	const INQUIRY_MESSAGE = 'What is it you do?';

	private $validator;

	/** @var OperatorMailer|\PHPUnit_Framework_MockObject_MockObject */
	private $operatorMailer;

	/** @var TemplateBasedMailerSpy */
	private $userMailer;

	public function setUp(): void {
		$this->validator = $this->newSucceedingValidator();
		$this->operatorMailer = $this->createMock( OperatorMailer::class );
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
		$this->operatorMailer->expects( $this->once() )
			->method( 'sendMailToOperator' )
			->with(
				$this->equalTo( new EmailAddress( self::INQUIRER_EMAIL_ADDRESS ) ),
				$this->equalTo( [
					'firstName' => self::INQUIRER_FIRST_NAME,
					'lastName' => self::INQUIRER_LAST_NAME,
					'emailAddress' => self::INQUIRER_EMAIL_ADDRESS,
					'subject' => self::INQUIRY_SUBJECT,
					'message' => self::INQUIRY_MESSAGE
				] )
			);

		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $this->newRequest() );
	}

	public function testGivenValidRequest_theUserIsNotified(): void {
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
			self::INQUIRY_SUBJECT,
			self::INQUIRY_MESSAGE
		);
	}

	private function newSucceedingValidator(): GetInTouchValidator {
		$validator = $this->getMockBuilder( GetInTouchValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$validator->method( 'validate' )->willReturn( new ValidationResult() );

		return $validator;
	}

}
