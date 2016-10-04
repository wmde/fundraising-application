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
	private $forwardMailer;

	/** @var TemplateBasedMailerSpy */
	private $confirmMailer;

	public function setUp() {
		$this->validator = $this->newSucceedingValidator();
		$this->forwardMailer = new TemplateBasedMailerSpy( $this );
		$this->confirmMailer = $this->newConfirmMailerDummy();
	}

	private function newGetInTouchUseCase(): GetInTouchUseCase {
		return new GetInTouchUseCase(
			$this->validator,
			$this->forwardMailer,
			$this->confirmMailer
		);
	}

	public function testGivenValidParameters_theyAreContainedInTheForwardingEmail() {
		$request = new GetInTouchRequest(
			self::INQUIRER_FIRST_NAME,
			self::INQUIRER_LAST_NAME,
			self::INQUIRER_EMAIL_ADDRESS,
			self::INQUIRY_SUBJECT,
			self::INQUIRY_MESSAGE
		);

		$useCase = $this->newGetInTouchUseCase();
		$useCase->processContactRequest( $request );
		$this->forwardMailer->assertCalledOnce();
		$this->forwardMailer->assertCalledOnceWith(
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

	private function newSucceedingValidator() {
		$validator = $this->getMockBuilder( GetInTouchValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$validator->method( 'validate' )->willReturn( new ValidationResult() );

		return $validator;
	}

	private function newConfirmMailerDummy() {
		return $this->getMockBuilder( TemplateBasedMailer::class )
			->disableOriginalConstructor()
			->getMock();
	}

}
