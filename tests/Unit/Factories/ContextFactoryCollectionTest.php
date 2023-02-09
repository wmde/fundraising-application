<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\Frontend\Factories\ContextFactoryCollection;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeEventSubscriber;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\ContextFactoryCollection
 */
class ContextFactoryCollectionTest extends TestCase {
	public function testGetDoctrineXMLMappingPathsCollectsPathsFromFactories(): void {
		// This class is only here to suppress the PHPUnit deprecation warning in this test.
		// When *all* contexts support the `getDoctrineMappingPaths` method, remove this and adapt the assertions
		// When AddressChangeContextFactory supports it but others don't, switch the class
		$legacyFactory = new AddressChangeContextFactory();

		$contextFactory1 = $this->createStub( BucketTestingContextFactory::class );
		$contextFactory1->method( 'getDoctrineMappingPaths' )->willReturn( [ '/path/to/bucket_mapping' ] );
		$contextFactory2 = $this->createStub( AutocompleteContextFactory::class );
		$contextFactory2->method( 'getDoctrineMappingPaths' )->willReturn( [ '/path/to/autocomplete_mapping' ] );
		// We're not testing factories that don't support getDoctrineMappingPaths,
		// since each modernization in a context would make the test fail
		$collection = new ContextFactoryCollection( $contextFactory1, $contextFactory2, $legacyFactory );

		$paths = $collection->getDoctrineXMLMappingPaths();

		$this->assertCount( 3, $paths );
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

	private function givenEventSubscribers() {
		return [
			new FakeEventSubscriber(),
			new FakeEventSubscriber(),
			new FakeEventSubscriber(),
		];
	}

	/**
	 * This is canary test to check if all context factories have been migrated to the new getDoctrineMappingPaths method
	 *
	 * If they are, this test should trigger a deprecation warning and the test will fail.
	 * You should then follow the refactoring instructions in the comments of ContextFactoryCollection and delete this test.
	 *
	 * See https://phabricator.wikimedia.org/T312080
	 *
	 * @return void
	 */
	public function testSomeBoundedContextsFactoriesAreStillLegacy(): void {
		$collection = new ContextFactoryCollection(
			new DonationContextFactory( [] ),
			new MembershipContextFactory( [] ),
			new AddressChangeContextFactory(),
			new SubscriptionContextFactory()
		);

		$collection->getDoctrineXMLMappingPaths();

		$this->expectNotToPerformAssertions();
	}
}
