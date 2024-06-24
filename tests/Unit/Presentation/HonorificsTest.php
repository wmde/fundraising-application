<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Honorifics;

#[CoversClass( Honorifics::class )]
class HonorificsTest extends TestCase {

	public function testGetKeys_returnsOnlyKeys(): void {
		$honorifics = new Honorifics( [ '' => 'None', 'Dr.' => 'Dr.' ] );
		$this->assertSame( [ '', 'Dr.' ], $honorifics->getKeys() );
	}

}
