<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\DataAccess;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext as DomainAuthenticationDomain;

class AuthenticationBoundedContext extends Type {
	public function getSQLDeclaration( array $column, AbstractPlatform $platform ): string {
		return 'VARCHAR(16)';
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function getName(): string {
		return 'AuthenticationBoundedContext';
	}

	public function convertToPHPValue( mixed $value, AbstractPlatform $platform ): DomainAuthenticationDomain {
		if ( !is_string( $value ) ) {
			throw new \InvalidArgumentException( 'Provided value must of the type string' );
		}
		return DomainAuthenticationDomain::from( $value );
	}

	public function convertToDatabaseValue( mixed $value, AbstractPlatform $platform ): string {
		if ( !$value instanceof DomainAuthenticationDomain ) {
			throw new \InvalidArgumentException( 'Provided value must of the type ' . DomainAuthenticationDomain::class . ' but is ' . gettype( $value ) );
		}

		return $value->value;
	}
}
