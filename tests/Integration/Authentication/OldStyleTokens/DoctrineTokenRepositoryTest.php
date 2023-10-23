<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Authentication\OldStyleTokens;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\DoctrineTokenRepository;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\SchemaCreator;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\DoctrineTokenRepository
 */
class DoctrineTokenRepositoryTest extends KernelTestCase {
	private EntityManager $entityManager;
	private SchemaCreator $schemaCreator;

	protected function setUp(): void {
		parent::setUp();
		self::bootKernel();
		$this->entityManager = $this->getContainer()->get( FunFunFactory::class )->getEntityManager();
		$this->schemaCreator = new SchemaCreator( $this->entityManager );
		$this->schemaCreator->createSchema();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->schemaCreator->dropSchema();
	}

	public function testStoreToken(): void {
		$token = new AuthenticationToken( 1, AuthenticationBoundedContext::Donation, 'give-me-access-123', 'let-me-change-things-987' );

		$repository = new DoctrineTokenRepository( $this->entityManager );
		$repository->storeToken( $token );

		$conn = $this->entityManager->getConnection();
		$result = $conn->executeQuery( 'SELECT * FROM legacy_auth_tokens WHERE id=1 AND authentication_context="D"' )->fetchAllAssociative();
		$this->assertCount( 1, $result );
		$this->assertSame( 'give-me-access-123', $result[0]['access_token'] );
		$this->assertSame( 'let-me-change-things-987', $result[0]['update_token'] );
	}

	public function testGetTokenById(): void {
		$expiry = new \DateTimeImmutable( '2021-01-01 12:00:00' );
		$token = new AuthenticationToken( 1, AuthenticationBoundedContext::Donation, 'give-me-access-123', 'let-me-change-things-987', $expiry );
		$repository = new DoctrineTokenRepository( $this->entityManager );
		$repository->storeToken( $token );
		$this->entityManager->clear();

		$retrievedToken = $repository->getTokenById( 1, AuthenticationBoundedContext::Donation );

		$this->assertSame( $token->getAccessToken(), $retrievedToken->getAccessToken() );
		$this->assertSame( $token->getUpdateToken(), $retrievedToken->getUpdateToken() );
		$this->assertSame( $token->authenticationBoundedContext, $retrievedToken->authenticationBoundedContext );
		$this->assertSame( $token->id, $retrievedToken->id );
		$this->assertFalse( $retrievedToken->updateTokenHasExpired( $expiry ) );
		$this->assertTrue( $retrievedToken->updateTokenHasExpired( $expiry->add( new \DateInterval( 'PT1S' ) ) ) );
	}

	public function testGetTokenByIdReturnsNullTokenWhenTokenWasNotFound(): void {
		$repository = new DoctrineTokenRepository( $this->entityManager );

		$this->assertInstanceOf(
			NullToken::class,
			$repository->getTokenById( 1, AuthenticationBoundedContext::Donation )
		);
	}

}
