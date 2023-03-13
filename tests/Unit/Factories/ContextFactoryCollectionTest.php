<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\Frontend\Factories\ContextFactoryCollection;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeEventSubscriber;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\ContextFactoryCollection
 */
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

	public function testNewEventSubscribersCollectsEventSubscribersFromFactories(): void {
		$eventSubscribers = $this->givenEventSubscribers();
		$contextFactory1 = $this->createStub( AddressChangeContextFactory::class );
		$contextFactory1->method( 'newEventSubscribers' )->willReturn( [ $eventSubscribers[0] ] );
		$contextFactory2 = $this->createStub( SubscriptionContextFactory::class );
		$contextFactory2->method( 'newEventSubscribers' )->willReturn( [ $eventSubscribers[1], $eventSubscribers[2] ] );
		$contextFactory3 = $this->createStub( AutocompleteContextFactory::class );
		$collection = new ContextFactoryCollection( $contextFactory1, $contextFactory2, $contextFactory3 );

		$collectedEventSubscribers = $collection->newEventSubscribers();

		$this->assertSame( $collectedEventSubscribers, $eventSubscribers );
	}

	/**
	 * @return FakeEventSubscriber[]
	 */
	private function givenEventSubscribers(): array {
		return [
			new FakeEventSubscriber(),
			new FakeEventSubscriber(),
			new FakeEventSubscriber(),
		];
	}
}
