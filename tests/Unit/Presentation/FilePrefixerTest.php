<?php

declare( strict_types = 1 );

namespace Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

class FilePrefixerTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoFilePrefixes_fileNameIsNotChanged() {
		$prefixer = new FilePrefixer( '' );
		$this->assertSame( 'wmde.js', $prefixer->prefixFile( 'wmde.js' ) );
	}

	public function testGivenPrefixes_prefixIsPrependedToFilename() {
		$prefixer = new FilePrefixer( 'badcaffee' );
		$this->assertSame( 'badcaffee.wmde.js', $prefixer->prefixFile( 'wmde.js' ) );
	}

	public function testGivenPrefixesWithPath_prefixIsPrependedToFilename() {
		$prefixer = new FilePrefixer( 'badcaffee' );
		$this->assertSame( 'res/js/badcaffee.wmde.js', $prefixer->prefixFile( 'res/js/wmde.js' ) );
	}

}
