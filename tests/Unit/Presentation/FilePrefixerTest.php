<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;

class FilePrefixerTest extends \PHPUnit\Framework\TestCase {

	public function testGivenNoFilePrefixes_fileNameIsNotChanged(): void {
		$prefixer = new FilePrefixer( '' );
		$this->assertSame( 'wmde.js', $prefixer->prefixFile( 'wmde.js' ) );
	}

	public function testGivenPrefixes_prefixIsPrependedToFilename(): void {
		$prefixer = new FilePrefixer( 'badcaffee' );
		$this->assertSame( 'badcaffee.wmde.js', $prefixer->prefixFile( 'wmde.js' ) );
	}

	public function testGivenPrefixesWithPath_prefixIsPrependedToFilename(): void {
		$prefixer = new FilePrefixer( 'badcaffee' );
		$this->assertSame( 'res/js/badcaffee.wmde.js', $prefixer->prefixFile( 'res/js/wmde.js' ) );
	}

}
