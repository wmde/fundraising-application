<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
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
class ContextFactoryCollection {
	/**
	 * @var AddressChangeContextFactory[]|DonationContextFactory[]|AutocompleteContextFactory[]|BucketTestingContextFactory[]|MembershipContextFactory[]|SubscriptionContextFactory[]
	 */
	private array $contextFactories;

	public function __construct( DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory|BucketTestingContextFactory|AutocompleteContextFactory ...$contextFactories ) {
		$this->contextFactories = $contextFactories;
	}

	/**
	 * Paths that can be used for {@see ORMSetup::createXMLMetadataConfiguration()}
	 *
	 * @return string[]
	 */
	public function getDoctrineXMLMappingPaths(): array {
		return [
			...$this->getDoctrineXMLMappingPathsFromSupportedFactories(),
			...$this->getDoctrineXMLMappingPathsFromLegacyFactories()
			];
	}

	/**
	 * Paths that can be used for {@see ORMSetup::createXMLMetadataConfiguration()}
	 *
	 * When https://phabricator.wikimedia.org/T312080 is done, you can inline this method
	 *
	 * @return string[]
	 */
	private function getDoctrineXMLMappingPathsFromSupportedFactories(): array {
		$paths = [];
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( method_exists( $contextFactory, 'getDoctrineMappingPaths' ) ) {
				array_push( $paths, ...$contextFactory->getDoctrineMappingPaths() );
			}
		}
		return array_unique( $paths );
	}

	/**
	 * This method is for backwards compatibility, until all context factories expose their
	 * Doctrine XML mapping paths via the "getDoctrineMappingPaths" method.
	 * When all classes support it, you can remove this method
	 *
	 * See https://phabricator.wikimedia.org/T312080
	 *
	 * @codeCoverageIgnore
	 * @return string[]
	 */
	private function getDoctrineXMLMappingPathsFromLegacyFactories(): array {
		$paths = [];
		$driverChain = $this->addLegacyMappingDrivers( new MappingDriverChain() );
		foreach ( $driverChain->getDrivers() as $driver ) {
			if ( !( $driver instanceof XmlDriver ) ) {
				throw new \InvalidArgumentException( 'Bounded contexts must use XML mapping drivers!' );
			}
			array_push( $paths, ...$driver->getLocator()->getPaths() );
		}
		return $paths;
	}

	/**
	 * This method is for backwards compatibility, until all context factories expose their
	 * Doctrine XML mapping paths via the "getDoctrineMappingPaths" method.
	 * When all classes support it, you can remove this method
	 *
	 * See https://phabricator.wikimedia.org/T312080
	 *
	 * @codeCoverageIgnore
	 * @param MappingDriverChain $driverChain
	 * @return MappingDriverChain
	 */
	private function addLegacyMappingDrivers( MappingDriverChain $driverChain ): MappingDriverChain {
		$skippedFactories = 0;
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( method_exists( $contextFactory, 'getDoctrineMappingPaths' ) ) {
				$skippedFactories++;
			} elseif ( method_exists( $contextFactory, 'visitMappingDriver' ) ) {
				$contextFactory->visitMappingDriver( $driverChain );
			} else {
				$driverChain->addDriver( $contextFactory->newMappingDriver(), $contextFactory::ENTITY_NAMESPACE );
			}
		}
		if ( $skippedFactories === count( $this->contextFactories ) ) {
			\trigger_error(
				'All Context Factories are now refactored - you can remove legacy code from ' . __CLASS__,
				\E_USER_DEPRECATED
			);
		}
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
}
