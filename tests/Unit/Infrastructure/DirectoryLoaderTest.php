<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use WMDE\Fundraising\Frontend\Infrastructure\DirectoryLoader;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\DirectoryLoader
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DirectoryLoaderTest extends \PHPUnit\Framework\TestCase {

	public function testGivenNonLocalResource_loaderThrowsException() {
		$dirLoader = new DirectoryLoader();
		$this->expectException( InvalidResourceException::class );
		$dirLoader->load( 'https://fancy.translation.cdn/', 'en_US' );
	}

	public function testGivenNonExistentPath_loaderThrowsException() {
		$dirLoader = new DirectoryLoader();
		$this->expectException( NotFoundResourceException::class );
		$dirLoader->load( '/path/does/not/exist', 'en_US' );
	}

}
