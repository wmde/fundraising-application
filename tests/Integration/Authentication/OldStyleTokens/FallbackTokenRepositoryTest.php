<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Authentication\OldStyleTokens;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\DonationContext\DataAccess\DonationData;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\DoctrineTokenRepository;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\FallbackTokenRepository;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTokenRepository;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineEntities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\DataAccess\MembershipApplicationData;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\FallbackTokenRepository
 */
class FallbackTokenRepositoryTest extends KernelTestCase {
	use RebuildDatabaseSchemaTrait;

	private EntityManager $entityManager;

	private const DONATION_ID = 1;
	private const MEMBERSHIP_ID = 8;

	public function setUp(): void {
		parent::setUp();
		static::bootKernel();
		$factory = static::getContainer()->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
	}

	public function testStoreTokenDelegatesToPrimaryRepository(): void {
		$token = new AuthenticationToken( self::DONATION_ID, AuthenticationBoundedContext::Donation, 'access', 'update' );
		$primary = new InMemoryTokenRepository();
		$repository = new FallbackTokenRepository( $primary, $this->entityManager );

		$repository->storeToken( $token );

		$this->assertTokenMatches( $token, $primary->getTokenById( self::DONATION_ID, AuthenticationBoundedContext::Donation ) );
	}

	public function testGivenExistingTokenForDonationItIsReturned(): void {
		$existingToken = new AuthenticationToken( self::DONATION_ID, AuthenticationBoundedContext::Donation, 'access', 'update' );
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$primaryRepository->storeToken( $existingToken );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );

		$token = $repository->getTokenById( self::DONATION_ID, AuthenticationBoundedContext::Donation );

		$this->assertTokenMatches( $existingToken, $token );
	}

	public function testGivenLegacyTokenForDonationItIsReturned(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );
		$donation = $this->givenDonationWithLegacyData( $this->givenDonationLegacyTokenData() );

		$token = $repository->getTokenById( $donation->getId(), AuthenticationBoundedContext::Donation );

		$expectedToken = new AuthenticationToken(
			$donation->getId(),
			AuthenticationBoundedContext::Donation,
			'legacy-update',
			'legacy-access'
		);
		$this->assertTokenMatches( $expectedToken, $token );
	}

	public function testGivenIncompleteLegacyTokenDataForDonationItReturnsNullToken(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );
		$donation = $this->givenDonationWithLegacyData( $this->givenIncompleteDonationLegacyTokenData() );

		$token = $repository->getTokenById( $donation->getId(), AuthenticationBoundedContext::Donation );

		$this->assertInstanceOf( NullToken::class, $token );
	}

	public function testGivenMissingTokenAndNonExistingDonationItReturnsNullToken(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );

		$token = $repository->getTokenById( self::DONATION_ID, AuthenticationBoundedContext::Donation );

		$this->assertInstanceOf( NullToken::class, $token );
	}

	public function testGivenExistingTokenForMembershipItIsReturned(): void {
		$existingToken = new AuthenticationToken( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership, 'access', 'update' );
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$primaryRepository->storeToken( $existingToken );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );

		$token = $repository->getTokenById( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership );

		$this->assertTokenMatches( $existingToken, $token );
	}

	public function testGivenLegacyTokenForMembershipItIsReturned(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );
		$this->givenMembershipWithLegacyData( $this->givenLegacyMembershipTokenData() );

		$token = $repository->getTokenById( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership );

		$expectedToken = new AuthenticationToken(
			self::MEMBERSHIP_ID,
			AuthenticationBoundedContext::Membership,
			'legacy-m-update',
			'legacy-m-access'
		);
		$this->assertTokenMatches( $expectedToken, $token );
	}

	public function testGivenIncompleteLegacyTokenForMembershipItReturnsNullToken(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );
		$this->givenMembershipWithLegacyData( $this->givenIncompleteLegacyMembershipTokenData() );

		$token = $repository->getTokenById( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership );

		$this->assertInstanceOf( NullToken::class, $token );
	}

	public function testGivenMissingTokenAndNonExistingMembershipItReturnsNullToken(): void {
		$primaryRepository = new DoctrineTokenRepository( $this->entityManager );
		$repository = new FallbackTokenRepository( $primaryRepository, $this->entityManager );

		$token = $repository->getTokenById( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership );

		$this->assertInstanceOf( NullToken::class, $token );
	}

	private function assertTokenMatches( AuthenticationToken $expectedToken, AuthenticationToken $actualToken ): void {
		$this->assertSame( $expectedToken->getAccessToken(), $actualToken->getAccessToken() );
		$this->assertSame( $expectedToken->getUpdateToken(), $actualToken->getUpdateToken() );
		$this->assertSame( $expectedToken->authenticationBoundedContext, $actualToken->authenticationBoundedContext );
		$this->assertSame( $expectedToken->id, $actualToken->id );
	}

	private function givenDonationWithLegacyData( DonationData $legacyTokenData ): Donation {
		$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();
		$donation->setDataObject( $legacyTokenData );
		$this->entityManager->persist( $donation );
		$this->entityManager->flush();
		return $donation;
	}

	private function givenIncompleteDonationLegacyTokenData(): DonationData {
		$legacyTokenData = new DonationData();
		$legacyTokenData->setUpdateToken( '' );
		$legacyTokenData->setAccessToken( 'legacy-access' );
		$legacyTokenData->setUpdateTokenExpiry( null );
		return $legacyTokenData;
	}

	private function givenDonationLegacyTokenData(): DonationData {
		$legacyTokenData = new DonationData();
		$legacyTokenData->setUpdateToken( 'legacy-update' );
		$legacyTokenData->setAccessToken( 'legacy-access' );
		$legacyTokenData->setUpdateTokenExpiry( '2038-01-01' );
		return $legacyTokenData;
	}

	private function givenMembershipWithLegacyData( MembershipApplicationData $legacyData ): MembershipApplication {
		$membership = ValidMembershipApplication::newDoctrineEntity( self::MEMBERSHIP_ID );
		$membership->setDataObject( $legacyData );
		$this->entityManager->persist( $membership );
		$this->entityManager->flush();
		return $membership;
	}

	private function givenLegacyMembershipTokenData(): MembershipApplicationData {
		$legacyTokenData = new MembershipApplicationData();
		$legacyTokenData->setAccessToken( 'legacy-m-access' );
		$legacyTokenData->setUpdateToken( 'legacy-m-update' );
		return $legacyTokenData;
	}

	private function givenIncompleteLegacyMembershipTokenData(): MembershipApplicationData {
		$legacyTokenData = new MembershipApplicationData();
		$legacyTokenData->setAccessToken( '' );
		$legacyTokenData->setUpdateToken( 'legacy-m-update' );
		return $legacyTokenData;
	}

}
