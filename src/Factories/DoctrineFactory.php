<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

class DoctrineFactory {
	private Connection $connection;
	private Configuration $config;
	private ContextFactoryCollection $contextFactories;

	private ?EntityManager $entityManager;

	public function __construct( Connection $connection, Configuration $config, ContextFactoryCollection $contextFactories ) {
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
		// While https://phabricator.wikimedia.org/T312080 is not complete,
		// we need to add drivers. When all factories support direct XML mapping,
		// delete the following line and change the environment factories to use
		// ORMSetup::createXMLMetadataConfiguration
		$this->config->setMetadataDriverImpl( $this->createMappingDriver() );
		return EntityManager::create( $this->getConnection(), $this->config );
	}

	private function createMappingDriver(): MappingDriver {
		$driver = new MappingDriverChain();
		// TODO initialize XML Driver for all context factories that support it
		return $this->contextFactories->addLegacyMappingDrivers( $driver );
	}

	public function setupEventSubscribers( EventManager $eventManager, EventSubscriber ...$additionalEventSubscribers ): void {
		$this->setupEventSubscriber( $eventManager, ...$this->contextFactories->newEventSubscribers() );
		$this->setupEventSubscriber( $eventManager, ...$additionalEventSubscribers );
	}

	private function setupEventSubscriber( EventManager $eventManager, EventSubscriber ...$eventSubscribers ): void {
		foreach ( $eventSubscribers as $subscriber ) {
			$eventManager->addEventSubscriber( $subscriber );
		}
	}
}
