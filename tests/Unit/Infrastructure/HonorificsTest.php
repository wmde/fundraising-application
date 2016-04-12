<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\Honorifics;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\Honorifics
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HonorificsTest extends \PHPUnit_Framework_TestCase {

	public function testGetKeys_returnsOnlyKeys() {
		$honorifics = new Honorifics( [ '' => 'None', 'Dr.' => 'Dr.' ] );
		$this->assertSame( [ '', 'Dr.' ], $honorifics->getKeys() );
	}

}
