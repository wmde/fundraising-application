<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\MailFormatter;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\MailFormatter
 */
class MailFormatterTest extends \PHPUnit\Framework\TestCase {

	public function testGivenLineWithLeadingSpaces_spacesAreTrimmed() {
		$this->assertSame(
			"This is a\ntest!	123 456789\n",
			MailFormatter::format( ' 		 This is a\ntest!	123 456789 ' )
		);
	}

	public function testGivenMultipleNewLines_lineBreaksAreLimitedToTwo() {
		$this->assertSame(
			"This is a\n\ntest!\n",
			MailFormatter::format( "This is a\n\n\n\n\n\ntest!" )
		);
	}

	public function testGivenBackslashN_charactersAreConvertedToLineBreaks() {
		$this->assertSame(
			"This\n\nis a\ntest\n",
			MailFormatter::format( 'This\\n\n is a\ntest' )
		);
	}

	public function testGivenMessage_lineBreakIsAppendedAtTheEnd() {
		$this->assertSame(
			"Line 1\nLine2\nLine3\n",
			MailFormatter::format( 'Line 1\nLine2\nLine3' )
		);
	}
}
