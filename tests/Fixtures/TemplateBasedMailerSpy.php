<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\GetInTouchMailerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\TemplateMailerInterface;

class TemplateBasedMailerSpy implements GetInTouchMailerInterface, TemplateMailerInterface {

	/**
	 * @var array<int, array{EmailAddress, array<string, mixed>}>
	 */
	private array $sendMailCalls = [];

	public function __construct( private readonly TestCase $testCase ) {
	}

	/**
	 * @param EmailAddress $recipient
	 * @param array<string, mixed> $templateArguments
	 */
	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ): void {
		$this->sendMailCalls[] = [ $recipient, $templateArguments ];
	}

	/**
	 * @return array<int, array{EmailAddress, array<string, mixed>}>
	 */
	public function getSendMailCalls(): array {
		return $this->sendMailCalls;
	}

	/**
	 * @param EmailAddress $expectedEmail
	 * @param array<string, mixed> $expectedArguments
	 */
	public function assertCalledOnceWith( EmailAddress $expectedEmail, array $expectedArguments ): void {
		$this->assertCalledOnce();

		$this->testCase->assertEquals(
			[
				$expectedEmail,
				$expectedArguments
			],
			$this->sendMailCalls[0]
		);
	}

	public function assertCalledOnce(): void {
		$this->testCase->assertCount( 1, $this->sendMailCalls, 'Mailer should be called exactly once' );
	}

}
