<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\Honorifics;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Honorifics
 */
class HonorificsTest extends \PHPUnit\Framework\TestCase {

	public function testGetKeys_returnsOnlyKeys(): void {
		$honorifics = new Honorifics( [ '' => 'None', 'Dr.' => 'Dr.' ] );
		$this->assertSame( [ '', 'Dr.' ], $honorifics->getKeys() );
	}

}
