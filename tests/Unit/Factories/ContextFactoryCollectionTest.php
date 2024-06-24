<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\Frontend\Factories\ContextFactoryCollection;

#[CoversClass( ContextFactoryCollection::class )]
class ContextFactoryCollectionTest extends TestCase {
	public function testGetDoctrineXMLMappingPathsCollectsPathsFromFactories(): void {
		$contextFactory1 = $this->createStub( BucketTestingContextFactory::class );
		$contextFactory1->method( 'getDoctrineMappingPaths' )->willReturn( [ '/path/to/bucket_mapping' ] );
		$contextFactory2 = $this->createStub( AutocompleteContextFactory::class );
		$contextFactory2->method( 'getDoctrineMappingPaths' )->willReturn( [ '/path/to/autocomplete_mapping' ] );
		$collection = new ContextFactoryCollection( $contextFactory1, $contextFactory2 );

		$paths = $collection->getDoctrineXMLMappingPaths();

		$this->assertCount( 2, $paths );
		$this->assertSame( '/path/to/bucket_mapping', $paths[0] );
		$this->assertSame( '/path/to/autocomplete_mapping', $paths[1] );
	}
}
