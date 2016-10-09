<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use PHPUnit_Framework_TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TemplateBasedMailerSpy extends TemplateBasedMailer {

	private $testCase;
	private $sendMailCalls = [];

	public function __construct( PHPUnit_Framework_TestCase $testCase ) {
		$this->testCase = $testCase;
	}

	public function sendMail( EmailAddress $recipient, array $templateArguments = [] ) {
		$this->sendMailCalls[] = [ $recipient, $templateArguments ];
	}

	public function getSendMailCalls(): array {
		return $this->sendMailCalls;
	}

	public function assertCalledOnceWith( EmailAddress $expectedEmail, array $expectedArguments ) {
		$this->assertCalledOnce();

		$this->testCase->assertEquals(
			[
				$expectedEmail,
				$expectedArguments
			],
			$this->sendMailCalls[0]
		);
	}

	public function assertCalledOnce() {
		$this->testCase->assertCount( 1, $this->sendMailCalls, 'Mailer should be called exactly once' );
	}

}