<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\Frontend\Autocomplete\AutocompleteContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\PaymentContext\PaymentContextFactory;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;
use function array_push;
use function array_unique;
use function method_exists;

/**
 * This is a collection of context factories of bounded contexts.
 *
 * It contains methods for calling methods in the instances, based on their capabilities.
 */
class ContextFactoryCollection {
	/**
	 * @var AddressChangeContextFactory[]|DonationContextFactory[]|AutocompleteContextFactory[]|BucketTestingContextFactory[]|MembershipContextFactory[]|SubscriptionContextFactory[]|PaymentContextFactory[]
	 */
	private array $contextFactories;

	public function __construct( DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory|BucketTestingContextFactory|AutocompleteContextFactory|PaymentContextFactory ...$contextFactories ) {
		$this->contextFactories = $contextFactories;
	}

	/**
	 * Paths that can be used for {@see ORMSetup::createXMLMetadataConfiguration()}
	 *
	 * @return string[]
	 */
	public function getDoctrineXMLMappingPaths(): array {
		$paths = [];
		foreach ( $this->contextFactories as $contextFactory ) {
			array_push( $paths, ...$contextFactory->getDoctrineMappingPaths() );
		}
		return [
			...array_unique( $paths ),
			];
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

	public function registerCustomTypes( Connection $connection ): void {
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( method_exists( $contextFactory, 'registerCustomTypes' ) ) {
				$contextFactory->registerCustomTypes( $connection );
			}
		}
	}
}
