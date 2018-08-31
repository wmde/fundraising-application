<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use org\bovigo\vfs\vfsStream;
use WMDE\Fundraising\Frontend\Infrastructure\FileStreamOpener;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\StreamOpeningError;

class StreamOpenerTest extends TestCase {

	private $root;

	/** @var FileStreamOpener */
	private $streamOpener;

	public function setUp() {
		$this->root = vfsStream::setup( 'testDir' );
		$this->streamOpener = new FileStreamOpener();
	}

	public function testGivenAValidURL_itReturnsAResource() {

		$this->assertTrue( is_resource( $this->streamOpener->openStream( vfsStream::url( 'testDir/some-file.txt' ), 'w' ) ) );
	}

	public function testGivenAnInvalidURL_itThrowsAnException() {
		$this->expectException( StreamOpeningError::class );
		$this->streamOpener->openStream( vfsStream::url( 'notValid/some-file.txt' ), 'w' );
	}

	public function testGivenANonExistingValidPath_pathIsCreated() {
		$this->streamOpener->openStream( vfsStream::url( 'testDir/deep/down/some-file.txt' ), 'w' );
		$this->assertFileExists( vfsStream::url( 'testDir/deep/down' ) );
	}

	public function testGivenANonExistingInvalidPath_itThrowsAnException() {
		$this->expectException( StreamOpeningError::class );
		$this->streamOpener->openStream( vfsStream::url( 'notValid/deep/down/some-file.txt' ), 'w' );
	}

	public function testGivenAppendMode_streamIsOpenedWithThatMode() {
		$stream = $this->streamOpener->openStream( vfsStream::url( 'testDir/deep/down/some-file.txt' ), 'a' );
		$info = stream_get_meta_data( $stream );

		$this->assertSame( 'a', $info['mode'] );
	}
}
