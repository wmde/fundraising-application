<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\ApplicationContext\Integration\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\GetInTouch\GetInTouchUseCase;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\ApplicationContext\UseCases\GetInTouch\GetInTouchUseCase
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCaseTest extends \PHPUnit_Framework_TestCase {

	const INQUIRER_FIRST_NAME = 'Curious';
	const INQUIRER_LAST_NAME = 'Guy';
	const INQUIRER_EMAIL_ADDRESS = 'curious.guy@alltheguys.com';
	const INQUIRY_SUBJECT = 'Please let me know';
	const INQUIRY_MESSAGE = 'What is it you do?';

	private $validator;

	/** @var TemplateBasedMailerSpy */
	private $operatorMailer;

	/** @var TemplateBasedMailerSpy */
	private $userMailer;

	public function setUp() {
		$this->validator = $this->newSucceedingValidator();
		$this->operatorMailer = new TemplateBasedMailerSpy( $this );
		$this->userMailer = new TemplateBasedMailerSpy( $this );
	}

	private function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->validator,
			$this->operatorMailer,
			$this->userMailer
		);
	}

	public function testGivenValidParameters_theyAreContainedInTheEmailToOperator() {
		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $this->newRequest() );
		$this->operatorMailer->assertCalledOnce();
		$this->operatorMailer->assertCalledOnceWith(
			new EmailAddress( self::INQUIRER_EMAIL_ADDRESS ),
			[
				'firstName' => self::INQUIRER_FIRST_NAME,
				'lastName' => self::INQUIRER_LAST_NAME,
				'emailAddress' => self::INQUIRER_EMAIL_ADDRESS,
				'subject' => self::INQUIRY_SUBJECT,
				'message' => self::INQUIRY_MESSAGE
			]
		);
	}

	public function testGivenValidRequest_theUserIsNotified() {
		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $this->newRequest() );
		$this->userMailer->assertCalledOnce();
		$this->userMailer->assertCalledOnceWith(
			new EmailAddress( self::INQUIRER_EMAIL_ADDRESS ),
			[]
		);
	}

	private function newRequest() {
		return new GetInTouchRequest(
			self::INQUIRER_FIRST_NAME,
			self::INQUIRER_LAST_NAME,
			self::INQUIRER_EMAIL_ADDRESS,
			self::INQUIRY_SUBJECT,
			self::INQUIRY_MESSAGE
		);
	}

	private function newSucceedingValidator() {
		$validator = $this->getMockBuilder( GetInTouchValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$validator->method( 'validate' )->willReturn( new ValidationResult() );

		return $validator;
	}

}
