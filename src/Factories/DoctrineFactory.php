<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;
use WMDE\Fundraising\DonationContext\DonationContextFactory;
use WMDE\Fundraising\Frontend\BannerImpressionData\BannerImpressionDataContextFactory;
use WMDE\Fundraising\Frontend\BucketTesting\BucketTestingContextFactory;
use WMDE\Fundraising\MembershipContext\MembershipContextFactory;
use WMDE\Fundraising\SubscriptionContext\SubscriptionContextFactory;

class DoctrineFactory {
	private Connection $connection;
	private Configuration $config;
	private array $contextFactories;

	private ?EntityManager $entityManager;

	/**
	 * @param Connection $connection
	 * @param Configuration $config
	 * @param DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory|BucketTestingContextFactory|BannerImpressionDataContextFactory ...$contextFactories
	 */
	public function __construct( Connection $connection, Configuration $config, ...$contextFactories ) {
		$this->connection = $connection;
		$this->config = $config;
		$this->contextFactories = $contextFactories;
		$this->entityManager = null;
	}

	public function getConnection(): Connection {
		return $this->connection;
	}

	public function getEntityManager(): EntityManager {
		if ( $this->entityManager === null ) {
			$this->entityManager = $this->newEntityManager();
		}
		return $this->entityManager;
	}

	private function newEntityManager(): EntityManager {
		AnnotationRegistry::registerLoader( 'class_exists' );
		$this->config->setMetadataDriverImpl( $this->createMappingDriver() );
		return EntityManager::create( $this->getConnection(), $this->config );
	}

	private function createMappingDriver(): MappingDriver {
		$driver = new MappingDriverChain();
		/** @var DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory $contextFactory */
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( method_exists( $contextFactory, 'visitMappingDriver' ) ) {
				$contextFactory->visitMappingDriver( $driver );
				continue;
			}
			$driver->addDriver( $contextFactory->newMappingDriver(), $contextFactory::ENTITY_NAMESPACE );
		}
		return $driver;
	}

	public function setupEventSubscribers( EventManager $eventManager, EventSubscriber ...$additionalEventSubscribers ): void {
		/** @var DonationContextFactory|MembershipContextFactory|SubscriptionContextFactory|AddressChangeContextFactory $contextFactory */
		foreach ( $this->contextFactories as $contextFactory ) {
			if ( !method_exists( $contextFactory, 'newEventSubscribers' ) ) {
				continue;
			}
			$this->setupEventSubscriber( $eventManager, ...array_values( $contextFactory->newEventSubscribers() ) );
		}
		$this->setupEventSubscriber( $eventManager, ...$additionalEventSubscribers );
	}

	private function setupEventSubscriber( EventManager $eventManager, EventSubscriber ...$eventSubscribers ): void {
		foreach ( $eventSubscribers as $subscriber ) {
			$eventManager->addEventSubscriber( $subscriber );
		}
	}
}
