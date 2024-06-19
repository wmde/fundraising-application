<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

class DoctrineTokenRepository implements TokenRepository {
	public function __construct( public readonly EntityManager $entityManager ) {
	}

	public function storeToken( AuthenticationToken $token ): void {
		$this->entityManager->persist( $token );
		$this->entityManager->flush();
	}

	public function getTokenById( int $id, AuthenticationBoundedContext $authenticationDomain ): AuthenticationToken {
		$conn = $this->entityManager->getConnection();

		/** @var array<string, int|string>[] $result */
		$result = $conn->executeQuery( 'SELECT * FROM legacy_auth_tokens WHERE id=:id AND authentication_context=:context LIMIT 1', [
			'id' => $id,
			'context' => $authenticationDomain->value
		] )->fetchAllAssociative();

		if ( count( $result ) === 0 ) {
			return new NullToken( $id, $authenticationDomain );
		}

		return new AuthenticationToken(
			(int)$result[0]['id'],
			AuthenticationBoundedContext::from( $result[0]['authentication_context'] ),
			(string)$result[0]['access_token'],
			(string)$result[0]['update_token'],
			$result[0]['update_token_expiry'] ? new \DateTimeImmutable( (string)$result[0]['update_token_expiry'] ) : null
		);
	}
}
