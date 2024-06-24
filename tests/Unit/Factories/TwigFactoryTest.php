<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Cache\FilesystemCache;
use Twig\Cache\NullCache;
use WMDE\Fundraising\Frontend\Factories\TwigFactory;

#[CoversClass( TwigFactory::class )]
class TwigFactoryTest extends TestCase {

	public function testEnabledCacheReturnsFilesystemCache(): void {
		$factory = new TwigFactoryTestImplementation( [ 'enable-cache' => true ], '/tmp' );

		$cache = $factory->getCache();

		$this->assertInstanceOf( FilesystemCache::class, $cache );
		$this->assertStringStartsWith( '/tmp/', $cache->generateKey( 'foo', '' ) );
	}

	public function testDisabledCacheReturnsNullCache(): void {
		$factory = new TwigFactoryTestImplementation( [ 'enable-cache' => false ], '/tmp' );

		$cache = $factory->getCache();

		$this->assertInstanceOf( NullCache::class, $cache );
	}

}
