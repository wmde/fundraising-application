<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;

#[CoversClass( MailFormatter::class )]
class MailFormatterTest extends TestCase {

	public function testGivenLineWithLeadingSpaces_spacesAreTrimmed(): void {
		$this->assertSame(
			"This is a\ntest!	123 456789\n",
			MailFormatter::format( ' 		 This is a\ntest!	123 456789 ' )
		);
	}

	public function testGivenMultipleNewLines_lineBreaksAreLimitedToTwo(): void {
		$this->assertSame(
			"This is a\n\ntest!\n",
			MailFormatter::format( "This is a\n\n\n\n\n\ntest!" )
		);
	}

	public function testGivenBackslashN_charactersAreConvertedToLineBreaks(): void {
		$this->assertSame(
			"This\n\nis a\ntest\n",
			MailFormatter::format( 'This\\n\nis a\ntest' )
		);
	}

	public function testGivenMessage_lineBreakIsAppendedAtTheEnd(): void {
		$this->assertSame(
			"Line 1\nLine2\nLine3\n",
			MailFormatter::format( 'Line 1\nLine2\nLine3' )
		);
	}
}
