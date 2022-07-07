<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

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
			$this->entityManager = EntityManager::create( $this->getConnection(), $this->config );
		}
		return $this->entityManager;
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
