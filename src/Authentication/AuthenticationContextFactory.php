<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class AuthenticationContextFactory {
	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	/**
	 * @return string[]
	 */
	public function getDoctrineMappingPaths(): array {
		return [ self::DOCTRINE_CLASS_MAPPING_DIRECTORY ];
	}

	public function registerCustomTypes( Connection $connection ): void {
		$this->registerAuthenticationDomain( $connection );
	}

	private function registerAuthenticationDomain( Connection $connection ): void {
		static $isRegistered = false;
		if ( $isRegistered ) {
			return;
		}
		Type::addType( 'AuthenticationBoundedContext', 'WMDE\Fundraising\Frontend\Authentication\DataAccess\AuthenticationBoundedContext' );
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping( 'AuthenticationBoundedContext', 'AuthenticationBoundedContext' );
		$isRegistered = true;
	}
}
