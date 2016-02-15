<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain;

use WMDE\Fundraising\Frontend\Domain\Honorifics;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Honorifics
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
