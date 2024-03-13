<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingMailerDecorator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ErrorThrowingTemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\ErrorHandlingMailerDecorator
 */
class ErrorHandlingMailerDecoratorTest extends TestCase {

	public function testOnSendMail_sendsMail(): void {
		$mailerSpy = new TemplateBasedMailerSpy( $this );
		$loggerSpy = new LoggerSpy();
		$email = new EmailAddress( 'happy@bunny.carrot' );
		$arguments = [ 'gotta get up' => 'to get down' ];

		$errorHandlingMailer = new ErrorHandlingMailerDecorator(
			$mailerSpy,
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( $email, $arguments );

		$mailerSpy->assertCalledOnceWith( $email, $arguments );
		$this->assertCount( 0, $loggerSpy->getLogCalls() );
	}

	public function testOnSendMailWithError_logsError(): void {
		$loggerSpy = new LoggerSpy();

		$errorHandlingMailer = new ErrorHandlingMailerDecorator(
			new ErrorThrowingTemplateBasedMailer(),
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( new EmailAddress( 'happy@bunny.carrot' ) );

		$this->assertCount( 1, $loggerSpy->getLogCalls() );
		$this->assertSame( [ ErrorThrowingTemplateBasedMailer::ERROR_MESSAGE ], $loggerSpy->getLogCalls()->getMessages() );
		$this->assertSame( LogLevel::ERROR, $loggerSpy->getFirstLogCall()->getLevel() );
	}

	public function testItLogsPreviousException(): void {
		$loggerSpy = new LoggerSpy();

		$errorHandlingMailer = new ErrorHandlingMailerDecorator(
			new ErrorThrowingTemplateBasedMailer( new \RuntimeException( 'Transport error - The mule ran away' ) ),
			$loggerSpy
		);

		$errorHandlingMailer->sendMail( new EmailAddress( 'happy@bunny.carrot' ) );

		$this->assertCount( 1, $loggerSpy->getLogCalls() );
		$this->assertStringContainsString( 'Transport error - The mule ran away', $loggerSpy->getLogCalls()->getMessages()[0] );
		$this->assertStringContainsString( ErrorThrowingTemplateBasedMailer::ERROR_MESSAGE, $loggerSpy->getLogCalls()->getMessages()[0] );
	}
}
