<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingTemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\TestDoubles\ErrorThrowingTemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\TestDoubles\TemplateBasedMailerSpy;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingTemplateBasedMailer
 */
class ErrorHandlingTemplateBasedMailerTest extends TestCase {

	public function testOnSendMail_sendsMail(): void {
		$mailerSpy = new TemplateBasedMailerSpy( $this );
		$loggerSpy = new LoggerSpy();
		$email = new EmailAddress( 'happy@bunny.carrot' );
		$arguments = [ 'gotta get up' => 'to get down' ];

		$errorHandlingMailer = new ErrorHandlingTemplateBasedMailer(
			$mailerSpy,
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( $email, $arguments );

		$mailerSpy->assertCalledOnceWith( $email, $arguments );
		$this->assertSame( 0, $loggerSpy->getLogCalls()->count() );
	}

	public function testOnSendMailWithError_logsError(): void {
		$loggerSpy = new LoggerSpy();

		$errorHandlingMailer = new ErrorHandlingTemplateBasedMailer(
			new ErrorThrowingTemplateBasedMailer(),
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( new EmailAddress( 'happy@bunny.carrot' ) );

		$this->assertSame( 1, $loggerSpy->getLogCalls()->count() );
		$this->assertSame( [ ErrorThrowingTemplateBasedMailer::ERROR_MESSAGE ], $loggerSpy->getLogCalls()->getMessages() );
		$this->assertSame( LogLevel::ERROR, $loggerSpy->getLogCalls()->getFirstCall()->getLevel() );
	}

	public function testItLogsPreviousException(): void {
		$loggerSpy = new LoggerSpy();

		$errorHandlingMailer = new ErrorHandlingTemplateBasedMailer(
			new ErrorThrowingTemplateBasedMailer( new \RuntimeException( 'Transport error - The mule ran away' ) ),
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( new EmailAddress( 'happy@bunny.carrot' ) );

		$this->assertSame( 1, $loggerSpy->getLogCalls()->count() );
		$this->assertStringContainsString( 'Transport error - The mule ran away', $loggerSpy->getLogCalls()->getMessages()[0] );
		$this->assertStringContainsString( ErrorThrowingTemplateBasedMailer::ERROR_MESSAGE, $loggerSpy->getLogCalls()->getMessages()[0] );
	}
}
