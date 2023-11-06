<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Doctrine\Common\EventManager;
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
			$connection = $this->getConnection();
			$this->entityManager = new EntityManager( $connection, $this->config, new EventManager() );
			$this->contextFactories->registerCustomTypes( $connection );
		}
		return $this->entityManager;
	}
}
