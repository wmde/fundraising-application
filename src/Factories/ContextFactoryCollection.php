<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;

/**
 * This is a collection of context factories of bounded contexts.
 *
 * It contains methods for calling methods in the instances, based on their capabilities.
 */
class ContextFactoryCollection implements \IteratorAggregate {
	/**
	 * @var AddressChangeContextFactory[]|DonationContextFactory[]|AutocompleteContextFactory[]|BucketTestingContextFactory[]|MembershipContextFactory[]|SubscriptionContextFactory[]
	 */
	private array $contextFactories;

	public function __construct( DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory|BucketTestingContextFactory|AutocompleteContextFactory ...$contextFactories ) {
		$this->contextFactories = $contextFactories;
	}

	/**
	 * This method is for backwards compatibility, until all context factories expose their
	 * Doctrine XML mapping paths via the "getDoctrineMappingPaths" method.
	 * When all classes support it, you can
	 *   - use ORMSetup::createXMLMetadataConfiguration( $factory->getDoctrineXmlMappingPaths() ) in the environment setup factories
	 *   - Remove createMappingDriver in DoctrineFactory
	 *   - Remove `addLegacyMappingDrivers`
	 * See https://phabricator.wikimedia.org/T312080
	 *
	 * @param MappingDriverChain $driverChain
	 * @return MappingDriverChain
	 */
	public function addLegacyMappingDrivers( MappingDriverChain $driverChain ): MappingDriverChain {
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( method_exists( $contextFactory, 'getDoctrineMappingPaths' ) ) {
				continue;
			} elseif ( method_exists( $contextFactory, 'visitMappingDriver' ) ) {
				$contextFactory->visitMappingDriver( $driverChain );
			} else {
				$driverChain->addDriver( $contextFactory->newMappingDriver(), $contextFactory::ENTITY_NAMESPACE );
			}
		}
		// TODO trigger deprecation warning if all factories supported getDoctrineMappingPaths
		return $driverChain;
	}

	/**
	 * @return EventSubscriber[]
	 */
	public function newEventSubscribers(): array {
		$eventSubscribers = [];
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( !method_exists( $contextFactory, 'newEventSubscribers' ) ) {
				continue;
			}
			array_push( $eventSubscribers, ...array_values( $contextFactory->newEventSubscribers() ) );
		}
		return $eventSubscribers;
	}

	public function getIterator(): \Iterator {
		return new \ArrayIterator( $this->contextFactories );
	}

}
